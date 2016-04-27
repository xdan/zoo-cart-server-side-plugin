<?php
/**
 * @copyright	Copyright (c) 2016 zoo. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * zoo - Zoo Cart Plugin
 *
 * @package		Joomla.Plugin
 * @subpakage	zoo.ZooCart
 */
class plgZooZooCart extends JPlugin {

	/**
	 * Constructor.
	 *
	 * @param 	$subject
	 * @param	array $config
	 */
	function __construct(&$subject, $config = array()) {
		// call parent constructor
		parent::__construct($subject, $config);
	}
	function onAjaxFeedBack() {
        return $this->onAjaxSendOrder('feedback', 'Сообщение с формы обратной связи %s', 'Ваше сообщение успешно отправлено. Пожалуйста, ожидайте ответа.', 'К сожалению, произошла ошибка отправки. Попробуйте позвонить по номеру, указанному на верху сайта.');
    }

	function onAjaxSendOrder($layout = 'order', $theme = 'Новый заказ на сайте %s', $success = 'Ваш заказ успешно отправлен. Пожалуйста, ожидайте ответа.', $error = 'К сожалению, произошла ошибка отправки заказа. Попробуйте позвонить по номеру, указанному на верху сайта.') {
        $result = (object)['error' => false];
        $session = jFactory::getSession();
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        $attaches = [];
        $carts = $session->get('cart', array());
        if (count($carts) || $layout !== 'order') {
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.folder');
            jimport('joomla.filesystem.path');
            $max = ini_get('upload_max_filesize');
     
            $app = JFactory::getApplication();
            $input = $app->input;
            $files = $input->files->get('attach');
            $company = $input->get('company', '', 'STRING');
            $fio = $input->get('fio', '', 'STRING');
            if (empty($fio)) {
                $result->error = 'Вы не заполнили поле ФИО';
                return $result;
            }
            $phone = $input->get('phone', '', 'STRING');
            if (empty($phone)) {
                $result->error = 'Вы не заполнили поле Телефон';
                return $result;
            }

            $city = $input->get('city', '', 'STRING');

            $mail = $input->get('mail', '', 'STRING');
            if (empty($mail) || !preg_match('#.+@.+#', $mail)) {
                $result->error = 'Вы заполнили поле: E-mail';
                return $result;
            }
            $bisnes_line_department = $input->get('bisnes_line_department', '', 'STRING');
            $message = $input->get('message', '', 'STRING');

            foreach ($files as $file)  {
                if (!empty($file['error'])) {
                    $result->error = 'Ошибка загрузки файла';
                    return $result;
                }

                if (!empty($file) and !empty($file['tmp_name']) and $file['size']) {
                    $filename = JFile::makeSafe($file['name']);

                    $robo = JPATH_ROOT . DS . "media" . DS .'zoo';
                    if (!is_dir($robo)) {
                        JFolder::create($robo, '0777');
                    }

                    $path = $robo.DS.'files';
                    if (!is_dir($path)) {
                        JFolder::create($path, '0777');
                    }
                    if (JPath::canChmod($path)) {
                        JPath::setPermissions($path, '0777', '0777');
                    }

                    $extension = JFile::getExt($file['name']);

                    if (in_array(strtolower($extension), array('php', 'phtml', 'exe', 'com'))) {
                        $result->error = 'Файл имеет запрещенный формат';
                        return $result;
                    }

                    do{
                        $randname = md5($filename.rand(100, 200)).'.'.$extension;
                    }while(file_exists($path . DS . $randname));

                    if (!JFile::upload($file['tmp_name'], $path . DS . $randname)) {
                        $result->error = 'Ошибка копирования запрещенного файла';
                        return $result;
                    }

                    $attaches[] = $path . DS . $randname;

                    if (JPath::canChmod($path)) {
                        JPath::setPermissions($path, '0644', '0744');
                    }
                }
            }
            $mailer = JFactory::getMailer(); // возвращает экземпляр jMail
            $mailer->isHTML(true);
            $mailer->Encoding = 'base64';
            $config = JFactory::getConfig();
            //$mail->sendMail($config->get('mailfrom'), $config->get('fromname'), $email, 'Новая заявка', '<p>'.$html.'</p>', true, null, null, $filename);
            $mailer->addRecipient([$config->get('mailfrom')]);
            $mailer->setSubject(sprintf($theme, $config->get('fromname')));

            ob_start();
            include $layout.'.php';
            $body = ob_get_clean();

            $mailer->setBody($body);
            foreach ($attaches as $attach) {                
                $mailer->addAttachment($attach);
            }
            if ($mailer->Send()) {
                $result->message = $success;
                if ($layout == 'order') {                    
                    $session->set('cart', array());
                }
            } else {
                $result->error = $error;
            }
        } else {
            $result->error = 'Ваша корзина пуста';
        }
        return $result;
    }
	function onAjaxSetCount() {
        $summ = $this->onAjaxZooCart(true);
        return [jFactory::getSession()->get('cart', array()), $summ];
    }
	function onAjaxClear() {
        jFactory::getSession()->set('cart', array());
    }
	function onAjaxDelCart() {
        $result = (object)['error' => false];
        $app = jFactory::getApplication();
        $input = $app->input;
        $session = jFactory::getSession();
        $carts = $session->get('cart', array());
        $item_id = $input->get('id', 0, 'INT');
        foreach ($carts as $i=>$cart) {
            if ($cart->item_id == $item_id) {
                unset($carts[$i]);
                break;
            }
        }

        $session->set('cart', $carts);
        return $this->getSumm();
    }
	function getSumm() {
        $result = (object)['error' => false];
        $session = jFactory::getSession();
        $carts = $session->get('cart', array());
        $result->summ = 0;
        $result->count = 0;
        foreach ($carts as $cart) {
            $result->summ += $cart->count * $cart->price;
            $result->count += $cart->count;
        }

        return $result;
    }
	function onAjaxZooCart($setcount = false) {
        $result = (object)['error' => false];
        $app = jFactory::getApplication();
        $input = $app->input;
        $session = jFactory::getSession();
        $carts = $session->get('cart', array());
        
        $item_id = $input->get('id', 0, 'INT');
        $count = $input->get('count', 1, 'INT') ?: 1;

        if (!$item_id) {
            $result->error = 'Нет данных';
            return $result;
        }
        require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');
        $zoo = App::getInstance('zoo');
        $application = $zoo->table->application->get(1);
        $item = $zoo->table->item->get($item_id);
        
        if (!$item->id) {
            $result->error = 'Нет данных';
            return $result;
        }
        
        // дергаем все элементы из позиции prices в layout prices
        $render = $zoo->renderer->create('item')->addPath(array($zoo->path->path('component.site:'), $application->getTemplate()->getPath()));
        $render->setItem($item);
        $render->setlayout('prices');
        $elements = $render->getPositionElements('prices');


        $finded = false;
        foreach ($carts as $cart) {
            if ($cart->item_id === $item->id) {
                if (!$setcount) {                    
                    $cart->count += $count;
                } else {
                    $cart->count = $count;
                }
                $finded = true;
                break;
            }
        }

        if (!$finded) {
            $cart = (object)[
                'item_id' => $item->id,
                'count' => $count,
            ];
            $carts[] = $cart;
        }

        $max = 1;

        foreach ($elements as $element) {
            if ($element->config->get('ext') <= $cart->count && $element->config->get('ext') >= $max && $element->get('value')) {
                $max = (int)$element->config->get('ext');
                $cart->price = $element->get('value');
            }
        }

        $result->summ = 0;
        $result->count = 0;
 
        foreach ($carts as $cart) {
            $result->summ += $cart->count * $cart->price;
            $result->count += $cart->count;
        }

        $session->set('cart', $carts);
        return $result;
    }
}