<?php
/**
 * Created by PhpStorm.
 * User: soft
 * Date: 10/25/16
 * Time: 8:06 PM
 */

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class Template extends ActiveRecord {

    const DOWNLOADS_DIR = '../downloads';
    const SESSION_TEMPLATE_ID = 'templateID';
    const FLASH_DOWNLOAD_TRIGGER = 'needDownloadFile';

    public static function tableName(){
        return '{{templates}}';
    }

    public function rules() {
        return [
            ['name, file_name, price', 'required'],
            ['name, file_name', 'string'],
            ['price', 'double'],
        ];
    }

    static function downloadFile($templateId){
        $template = self::findOne( $templateId );
        if(!empty($template)) {
            $file = self::getDownloadsDir().$template->file_name;
            Yii::$app->response->sendFile($file);
        }
    }

    static function getDownloadsDir(){
        return Yii::$app->basePath . '/'.self::DOWNLOADS_DIR.'/';
    }
}