<?php
namespace Drupal\community_notifier\Plugin\QueueWorker;

use Drupal\Core\Mail\MailManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 *
 * @QueueWorker(
 * id = "email_queue",
 * title = "Community notifier queue processor",
 * cron = {"time" = 90}
 * )
 */
class EmailQueue extends QueueWorkerBase implements ContainerFactoryPluginInterface {
  private $mail;
  public function __construct(MailManager $mail) {
    $this->mail = $mail;
  }

  public function processItem($data) {
    //$to = \Drupal::config('system.site')->get('mail');
    $params = $data;
    $this->mail->mail('community_notifier','query_mail',$data['to'],'en',$params,NULL,true);
  }
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('plugin.manager.mail')
    );
  }
}