<?php
declare(strict_types=1);

namespace Src\ChinaMobile;

use Src\AbstractTv;
use Src\ChinaMobile\Driver\BeijingDriver;
use Src\Constant\ChinaMobileConstant;
use Src\Constant\EpgConstant;

class ChinaMobile extends AbstractTv
{
    /** @var string 历史 json 路径 */
    protected $historyJsonPath = ChinaMobileConstant::JSON_PATH;

    /**
     * @inheritDoc
     */
    protected function init(): void
    {
        $drivers = [
            BeijingDriver::class
        ];
        $this->setDrivers($drivers);
    }

    /**
     * @inheritDoc
     */
    function getTvM3uContent(string $url, string $groupPrefix = ''): string
    {
        $tvList = require(BASE_PATH . ChinaMobileConstant::TV_LIST);

        $epgList = require(BASE_PATH . EpgConstant::EPG_LIST);
        $epgTvgNameList = array_column($epgList, 'tvg-name');

        $content = '';
        foreach ($tvList as $tv) {
            $idx = array_search($tv['tvg-name'], $epgTvgNameList);
            $tvg = ($idx !== false) ? array_merge($tv, $epgList[$idx]) : $tv;

            if (!isset($tvg['group-title']) || !in_array($tvg['group-title'], ['央视', '卫视', 'NewTV'])) {
                $tvg['group-title'] = '卫视';
            }

            if ($groupPrefix) {
                $tvg['group-title'] = $groupPrefix . '·' . $tvg['group-title'];
            }

            $content .= $this->getM3uLine(
                $tvg['name'] ?? $tvg['tvg-name'],
                $tvg['url'],
                $tvg['group-title'] ?? '',
                $tvg['tvg-name'] ?? '',
                $tvg['tvg-id'] ?? '',
                $tvg['tvg-logo'] ?? ''
            );
        }
        return $content;
    }
}