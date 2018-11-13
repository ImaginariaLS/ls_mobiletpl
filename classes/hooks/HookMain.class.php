<?php

class PluginMobiletpl_HookMain extends Hook
{
    const ConfigKey = 'mobiletpl';
    const HooksArray = [
        'viewer_init_start'  =>  'ViewerInitStart',
        'lang_init_start' => 'LangInitStart',
        'template_footer_menu_navigate_item' => 'MenuItem',
        'template_profile_whois_activity_item' => 'WhoisActivityItem',
        'init_action' => 'InitAction',
        'topic_show' => 'TopicShow',

    ];

    protected $bIsNeedShowMobile = null;

    public function RegisterHook()
    {
        $plugin_config_key = $this::ConfigKey;
        foreach ($this::HooksArray as $hook => $callback) {
            $this->AddHook(
                $hook,
                $callback,
                __CLASS__,
                Config::Get("plugin.{$plugin_config_key}.hook_priority.{$hook}") ?? 1
            );
        }
    }

    public function InitAction()
    {
        $oUserCurrent = $this->User_GetUserCurrent();
        if (!$oUserCurrent) {
            return;
        }
        if (!MobileDetect::IsMobileTemplate()) {
            return;
        }
        /**
         * Загружаем в шаблон необходимые переменные
         */
        $iCountTopicFavourite = $this->Topic_GetCountTopicsFavouriteByUserId($oUserCurrent->getId());
        $iCountTopicUser = $this->Topic_GetCountTopicsPersonalByUser($oUserCurrent->getId(), 1);
        $iCountCommentUser = $this->Comment_GetCountCommentsByUserId($oUserCurrent->getId(), 'topic');
        $iCountCommentFavourite = $this->Comment_GetCountCommentsFavouriteByUserId($oUserCurrent->getId());
        $iCountNoteUser = $this->User_GetCountUserNotesByUserId($oUserCurrent->getId());

        $this->Viewer_Assign('iCountWallUserCurrent', $this->Wall_GetCountWall(array('wall_user_id' => $oUserCurrent->getId(), 'pid' => null)));
        /**
         * Общее число публикация и избранного
         */
        $this->Viewer_Assign('iCountCreatedUserCurrent', $iCountNoteUser + $iCountTopicUser + $iCountCommentUser);
        $this->Viewer_Assign('iCountFavouriteUserCurrent', $iCountCommentFavourite + $iCountTopicFavourite);
        $this->Viewer_Assign('iCountFriendsUserCurrent', $this->User_GetCountUsersFriend($oUserCurrent->getId()));
    }

    /**
     * Инициализация
     */
    public function ViewerInitStart($aParams)
    {
        if (MobileDetect::IsMobileTemplate()) {
            Config::Set('view.skin', Config::Get('plugin.mobiletpl.template'));
        }
    }

    /**
     * Инициализация
     */
    public function LangInitStart()
    {
        if (MobileDetect::IsMobileTemplate()) {
            Config::Set('view.skin', Config::Get('plugin.mobiletpl.template'));
        }
    }

    public function MenuItem()
    {
        if (!$this->PluginMobiletpl_Main_IsMobileTemplate()) {
            return $this->Viewer_Fetch(Plugin::GetTemplatePath(__CLASS__) . 'inject.navigate-item.tpl');
        }
    }

    public function WhoisActivityItem($aParams)
    {
        if ($this->PluginMobiletpl_Main_IsMobileTemplate()) {
            /**
             * Получаем последний топик пользователя
             */
            $oUser = $aParams['oUserProfile'];
            $oTopic = $this->PluginMobiletpl_Main_GetTopicLastbyUserId($oUser->getId());
            $this->Viewer_Assign('oTopicUserProfileLast', $oTopic);
            return $this->Viewer_Fetch(Plugin::GetTemplatePath(__CLASS__) . 'inject.profile.whois.activity-item.tpl');
        }
    }

    public function TopicShow($aParams)
    {
        $oTopic = $aParams['oTopic'];

        /**
         * Если активен плагин "ViewCount", то ничего не делаем
         */
        $aPlugins = Engine::getInstance()->GetPlugins();
        if (array_key_exists('viewcount', $aPlugins)) {
            return;
        }

        /**
         * Если топик просматривает его автор - пропускаем
         */
        $oUserCurrent = $this->User_GetUserCurrent();
        if ($oUserCurrent and $oUserCurrent->getId() == $oTopic->getUserId()) {
            return;
        }
        $this->PluginMobiletpl_Main_IncTopicCountRead($oTopic);
    }

}