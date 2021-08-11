<?php
namespace Digraph\Modules\event_convocations;

use Digraph\Users\GroupSources\AbstractGroupSource;

class GroupSource extends AbstractGroupSource
{
    public function groups(string $id): ?array
    {
        //if ID isn't a NetID, short circuit, this source knows nothing
        $id = explode('@', $id);
        $provider = array_pop($id);
        if ($provider != 'netid') {
            return [];
        }
        $netid = array_shift($id);
        //check cache
        $cache = $this->cms->cache();
        $cacheID = md5(static::class) . '.' . $netid;
        $citem = $cache->getItem($cacheID);
        //load and save into cache if cache isn't hit
        if (!$citem->isHit()) {
            $search = $this->cms->factory()->search();
            $search->where('${dso.type} = :type AND ${netid} = :netid');
            $search->limit(1);
            $data = !!$search->execute(['type' => 'convocation-coord', 'netid' => $netid]);
            $citem->set($data);
            $citem->expiresAfter(3600);
            $cache->save($citem);
        }
        //return
        return $citem->get() ? ['convocation-coordinator'] : [];
    }
}
