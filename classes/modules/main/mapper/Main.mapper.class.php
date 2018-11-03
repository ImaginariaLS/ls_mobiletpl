<?php
/*-------------------------------------------------------
*
*   LiveStreet Engine Social Networking
*   Copyright © 2008 Mzhelskiy Maxim
*
*--------------------------------------------------------
*
*   Official site: www.livestreet.ru
*   Contact e-mail: rus.engine@gmail.com
*
*   GNU General Public License, version 2:
*   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
---------------------------------------------------------
*/

class PluginMobiletpl_ModuleMain_MapperMain extends Mapper
{


    public function IncTopicCountRead($iId)
    {
        $table = Config::Get('db.table.topic');

        $sql = "update {$table} SET topic_count_read=topic_count_read+1 WHERE topic_id = ?d ";
        return $aRow = $this->oDb->query($sql, $iId);
    }
}