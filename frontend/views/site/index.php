<?php

/* @var $this yii\web\View */

use yii\web\View;
use yii\helpers\Url;
use common\models\Template;

$this->title = 'My Yii Application';

if( Yii::$app->session->hasFlash(Template::FLASH_DOWNLOAD_TRIGGER) )
{
    $this->registerJs('
        $(function(){
            var $frame = $("<iframe class=\'hidden\' src=\''.Url::to('site/download').'\' />");
            $("body").append($frame);
            setTimeout(function(){
                $frame.remove();
            }, 1000);
        });
    ', View::POS_END);
}
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Our Templates The Best!</h1>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-4">
                <h2>Template #1</h2>
                <img class="img-responsive" src="https://readytheme.net/wp-content/uploads/2015/11/thumbnail-4.png">
                <br/>
                <p>
                    <a class="btn btn-primary btn-block" href="<?= Yii::$app->urlManager->createUrl(['payment/pay', 'templateId' => 1]) ?>">
                        Buy Now
                    </a>
                </p>
            </div>
            <div class="col-lg-4">
                <h2>Template #2</h2>
                <img class="img-responsive" src="http://beautiful-templates.com/evo/wp-content/uploads/2013/12/hexagon.png">
                <br/>
                <p>
                    <a class="btn btn-primary btn-block" href="<?= Yii::$app->urlManager->createUrl(['payment/pay', 'templateId' => 2]) ?>">
                        Buy Now
                    </a>
                </p>
            </div>
            <div class="col-lg-4">
                <h2>Template #3</h2>
                <img class="img-responsive" src="https://w3layouts.com/wp-content/uploads/2015/07/MyTemplate.jpg">
                <br/>
                <p>
                    <a class="btn btn-primary btn-block" href="<?= Yii::$app->urlManager->createUrl(['payment/pay', 'templateId' => 3]) ?>">
                        Buy Now
                    </a>
                </p>
            </div>
        </div>

    </div>
</div>
