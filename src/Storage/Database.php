<?php

namespace Drupal\wmpage_cache\Storage;

use Drupal\Core\Database\Connection;
use Drupal\wmpage_cache\Cache;
use Drupal\wmpage_cache\CacheSerializerInterface;
use Drupal\wmpage_cache\Exception\NoSuchCacheEntryException;

class Database implements StorageInterface
{
    public const TX = 'wmpage_cache_storage';
    public const TABLE_ENTRIES = 'wmpage_cache';
    public const TABLE_TAGS = 'wmpage_cache_tags';

    /** @var \Drupal\Core\Database\Connection */
    protected $db;
    /** @var \Drupal\wmpage_cache\CacheSerializerInterface */
    protected $serializer;

    public function __construct(
        Connection $db,
        CacheSerializerInterface $serializer
    ) {
        $this->db = $db;
        $this->serializer = $serializer;
    }

    public function load($id, $includeBody = true)
    {
        $item = $this->loadMultiple([$id], $includeBody)->current();
        if (!$item) {
            throw new NoSuchCacheEntryException($id);
        }

        return $item;
    }

    public function loadMultiple(array $ids, $includeBody = true): \Iterator
    {
        if (empty($ids)) {
            return;
        }

        $fields = ['id', 'uri', 'method', 'expiry'];
        if ($includeBody) {
            $fields[] = 'content';
            $fields[] = 'headers';
        }

        if (!$this->db->schema()->tableExists(self::TABLE_ENTRIES)) {
            throw new NoSuchCacheEntryException(
                reset($ids),
                'Cache entry table does not exist.'
            );
        }

        $stmt = $this->db->select(self::TABLE_ENTRIES, 'c')
            ->fields('c', $fields)
            ->condition('c.id', $ids, 'IN')
            ->condition('c.expiry', time(), '>=')
            ->execute();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            yield $this->assocRowToEntry($row);
        }
    }

    public function set(Cache $item, array $tags)
    {
        $id = $item->getId();

        if (!$this->db->schema()->tableExists(self::TABLE_ENTRIES)) {
            return;
        }

        if (!$this->db->schema()->tableExists(self::TABLE_TAGS)) {
            return;
        }

        $tx = $this->db->startTransaction(self::TX);
        $tags = array_unique($tags);

        try {
            // Add cache entry
            $this->db->upsert(self::TABLE_ENTRIES)
                ->key($id)
                // TODO: add validation that the serializer made an associative
                // array with all the necessary fields.
                ->fields($this->serializer->normalize($item))
                ->execute();

            // Delete old tags
            $this->db->delete(self::TABLE_TAGS)
                ->condition('id', $id)
                ->execute();

            // Add new tags
            $insert = $this->db->insert(self::TABLE_TAGS)
                ->fields(['id', 'tag']);

            foreach ($tags as $tag) {
                $insert->values([$id, $tag]);
            }

            $insert->execute();
        } catch (\Exception $e) {
            $tx->rollback();
            // TODO add the fact that we rollbacked to the exception.
            throw $e;
        }

        unset($tx); // commit, btw this is marginaal AS FUCK.
    }

    public function getExpired($amount)
    {
        if (!$this->db->schema()->tableExists(self::TABLE_ENTRIES)) {
            return [];
        }

        $q = $this->db->select(self::TABLE_ENTRIES, 'c')
            ->fields('c', ['id']);
        $q->condition('c.expiry', time(), '<');
        $q->range(0, (int) $amount);

        return $q->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getByTags(array $tags): array
    {
        if (!$tags) {
            return [];
        }

        if (!$this->db->schema()->tableExists(self::TABLE_ENTRIES)) {
            return [];
        }

        if (!$this->db->schema()->tableExists(self::TABLE_TAGS)) {
            return [];
        }

        $q = $this->db->select(self::TABLE_ENTRIES, 'c')
            ->fields('c', []);
        $q->condition('c.expiry', time(), '>=');
        $q->innerJoin(self::TABLE_TAGS, 't', 't.id = c.id');
        $q->condition('t.tag', $tags, 'IN');

        return array_map(
            static function (array $data) {
                return new Cache(
                    $data['id'],
                    $data['uri'],
                    $data['method'],
                    $data['content'],
                    unserialize($data['headers'], ['allowed_classes' => false]),
                    $data['expiry']
                );
            },
            $q->execute()->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    public function remove(array $ids)
    {
        if (empty($ids)) {
            return;
        }

        if (!$this->db->schema()->tableExists(self::TABLE_ENTRIES)) {
            return;
        }

        if (!$this->db->schema()->tableExists(self::TABLE_TAGS)) {
            return;
        }

        $tx = $this->db->startTransaction(self::TX);

        try {
            $this->db->delete(self::TABLE_ENTRIES)
                ->condition('id', $ids, 'IN')
                ->execute();

            $this->db->delete(self::TABLE_TAGS)
                ->condition('id', $ids, 'IN')
                ->execute();
        } catch (\Exception $e) {
            $tx->rollback();
            // TODO add the fact that we rollbacked to the exception.
            throw $e;
        }

        unset($tx); // commit, btw this is marginaal AS FUCK.
    }

    public function flush()
    {
        if (!$this->db->schema()->tableExists(self::TABLE_ENTRIES)) {
            return;
        }

        // Keep it transactional or risk a race with truncate?
        $ids = $this->db->select(self::TABLE_ENTRIES, 'c')
            ->fields('c', ['id'])
            ->execute()->fetchCol();

        while (!empty($ids)) {
            $this->remove(array_splice($ids, 0, 50));
        }
    }

    protected function assocRowToEntry(array $row)
    {
        return $this->serializer->denormalize($row);
    }
}
