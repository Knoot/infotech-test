<?php

namespace app\service\cache;

class TopAuthorsCacheService
{
    public const CACHE_KEY = 'top-authors';

    /**
     * @param int[] $years
     *
     * @return void
     */
    public static function invalidateByYears(array $years): void
    {
        $cache = \Yii::$app->cache;
        foreach ($years as $year) {
            $cache->delete(self::getCacheKey(['year' => (int) $year]));
        }
    }

    public static function getCacheKey($keys): array
    {
        return array_merge([self::CACHE_KEY], $keys);
    }
}
