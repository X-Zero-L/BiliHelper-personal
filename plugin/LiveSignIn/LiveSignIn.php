<?php declare(strict_types=1);

/**
 *  Website: https://mudew.com/
 *  Author: Lkeme
 *  License: The MIT License
 *  Email: Useri@live.cn
 *  Updated: 2022 ~ 2023
 *
 *   _____   _   _       _   _   _   _____   _       _____   _____   _____
 *  |  _  \ | | | |     | | | | | | | ____| | |     |  _  \ | ____| |  _  \ &   ／l、
 *  | |_| | | | | |     | | | |_| | | |__   | |     | |_| | | |__   | |_| |   （ﾟ､ ｡ ７
 *  |  _  { | | | |     | | |  _  | |  __|  | |     |  ___/ |  __|  |  _  /  　 \、ﾞ ~ヽ   *
 *  | |_| | | | | |___  | | | | | | | |___  | |___  | |     | |___  | | \ \   　じしf_, )ノ
 *  |_____/ |_| |_____| |_| |_| |_| |_____| |_____| |_|     |_____| |_|  \_\
 */

use Bhp\Api\XLive\ApiXLiveSign;
use Bhp\Log\Log;
use Bhp\Plugin\BasePlugin;
use Bhp\Plugin\Plugin;
use Bhp\TimeLock\TimeLock;

class LiveSignIn extends BasePlugin
{
    /**
     * 插件信息
     * @var array|string[]
     */
    protected ?array $info = [
        'hook' => __CLASS__, // hook
        'name' => 'LiveSignIn', // 插件名称
        'version' => '0.0.1', // 插件版本
        'desc' => '直播签到', // 插件描述
        'author' => 'Lkeme',// 作者
        'priority' => 1103, // 插件优先级
        'cycle' => '24(小时)', // 运行周期
    ];

    /**
     * @param Plugin $plugin
     */
    public function __construct(Plugin &$plugin)
    {
        //
        TimeLock::initTimeLock();
        // $this::class
        $plugin->register($this, 'execute');
    }

    /**
     * @use 执行
     * @return void
     */
    public function execute(): void
    {
        if (TimeLock::getTimes() > time() || !getEnable('live_sign_in')) return;
        //
        if ($this->signInTask()) {
            TimeLock::setTimes(24 * 60 * 60);
        } else {
            TimeLock::setTimes(mt_rand(6, 8) * 60 * 60);
        }
    }

    /**
     * @return bool
     */
    protected function signInTask(): bool
    {
        // {"code":0,"message":"0","ttl":1,"data":{"text":"","specialText":"","status":0,"allDays":30,"curMonth":6,"curYear":2022,"curDay":4,"curDate":"2022-6-4","hadSignDays":0,"newTask":0,"signDaysList":[],"signBonusDaysList":[]}}
        $response = ApiXLiveSign::webGetSignInfo();
        //
        if ($response['code']) {
            Log::warning("直播签到: 获取签到信息失败 {$response['code']} -> {$response['message']}");
            return false;
        }
        //
        if ($response['data']['status'] == 1) {
            Log::notice("直播签到: 今日已经签到过了哦~ 已连续签到 {$response['data']['hadSignDays']} 天");
            return true;
        }
        //
        $response = ApiXLiveSign::doSign();
        if ($response['code']) {
            Log::warning("直播签到: 签到失败 {$response['code']} -> {$response['message']}");
            return false;
        }
        //
        Log::notice("直播签到: 签到成功~ 连续签到 {$response['data']['hadSignDays']} 天 {$response['data']['specialText']}");
        return true;
    }

}
 