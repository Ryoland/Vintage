<?php

    namespace Vintage\A\Util {

        use \Vintage\C\PHP\VArray as VArray;

        abstract class Mail extends \Vintage\A\Lib {

            public static $HOSTS_SMTP = array();

            private static $SMTP_CHARSET             = 'ISO-2022-JP';
            private static $SMTP_ENCODING            = 'utf8';
            private static $SMTP_HEADER_CONTENT_TYPE = [
              'plain' => 'text/plain; charset=ISO-2022-JP',
              'text'  => 'text/plain; charset=ISO-2022-JP',
              'html'  => 'text/html; charset=ISO-2022-JP',
            ];
            private static $SMTP_HEADER_X_MAILER     = 'Vintage';
            private static $SMTP_LANGUAGE            = 'ja';

            final private function host_smtp() {

                $host = null;

                if (!empty(static::$HOSTS_SMTP)) {

                    $hosts = array();

                    foreach (static::$HOSTS_SMTP as $HOST) {

                        $weight = $HOST['weight'];

                        for ($i = 0; $i < $weight; $i++) {
                            $hosts[] = $HOST;
                        }
                    }

                    $count = count($hosts);
                    $rand  = mt_rand(0, --$count);
                    $host  = $hosts[$rand];
                }

                return $host;
            }

            final public function send(array $a) {









        $format      = @$a['format']      ?: 'text';
        $attachments = @$a['attachments'] ?: [];









                $from    = $a['from'];
                $to      = $a['to'];
                $subject = $a['subject'];
                $message = $a['message'];

                $name = isset($a['name']) ? $a['name'] : null;
                $cc   = isset($a['cc'])   ? $a['cc']   : array();
                $bcc  = isset($a['bcc'])  ? $a['bcc']  : array();

                $res = array();

                if (static::BACKEND == 'smtp') {

                    $host = $this->host_smtp();

                    $res = self::send_smtp(array(









            'format'      => $format,
            'attachments' => $attachments,









                        'from'    => $from,
                        'to'      => $to,
                        'subject' => $subject,
                        'message' => $message,
                        'host'    => $host,
                        'name'    => $name,
                        'cc'      => $cc,
                        'bcc'     => $bcc
                    ));

                } else { throw new \Exception(); }

                return $res;
            }

            final public static function send_smtp(array $a) {









        $format      = @$a['format']      ?: 'text';
        $attachments = @$a['attachments'] ?: [];









                $from    = $a['from'];
                $to      = $a['to'];
                $subject = $a['subject'];
                $message = $a['message'];
                $host    = $a['host'];

                $name    = isset($a['name'])    ? $a['name']    : null;
                $cc      = isset($a['cc'])      ? $a['cc']      : array();
                $bcc     = isset($a['bcc'])     ? $a['bcc']     : array();
                $charset = isset($a['charset']) ? $a['charset'] : self::$SMTP_CHARSET;
                $headers = isset($a['headers']) ? $a['headers'] : array();

                $tos        = VArray::is($to)  ? $to  : array($to);
                $ccs        = VArray::is($cc)  ? $cc  : array($cc);
                $bccs       = VArray::is($bcc) ? $bcc : array($bcc);
                $recipients = array_merge($tos, $ccs, $bccs);

                $mb_language_tmp = mb_language();
                $mb_internal_encoding_tmp = mb_internal_encoding();

                mb_language(self::$SMTP_LANGUAGE);
                mb_internal_encoding(self::$SMTP_ENCODING);

                $name    = isset($name) ? mb_encode_mimeheader($name)     : $name;
                $label   = isset($name) ? sprintf('%s<%s>', $name, $from) : $from;
                $subject = mb_encode_mimeheader($subject);
//              $message = mb_convert_encoding($message, $charset, 'auto');

                $params = array(
                    'host'     => $host['host'],
                    'port'     => $host['port'],
                    'auth'     => $host['auth'],
                    'username' => $host['user'],
                    'password' => $host['pass']
                );

                $headers = array_merge(array(
                    'Return-Path'  => $from,
                    'Content-Type' => self::$SMTP_HEADER_CONTENT_TYPE[$format],
                    'X-Mailer'     => self::$SMTP_HEADER_X_MAILER
                ), $headers);

                $headers['From']    = $label;
                $headers['To']      = implode(',', $tos);
                $headers['Cc']      = implode(',', $ccs);
                $headers['Subject'] = $subject;









        // $Mail_Mime
        $Mail_Mime = null;
        $ir_mime   = false;

        if ($format == 'html') {
          $ir_mime = true;
        }
        elseif (!empty($attachments)) {
          $ir_mime = true;
        }

        if ($ir_mime) {

          require_once('Mail/mime.php');

          $Mail_Mime = new \Mail_Mime("\n");
        }
        ///

        // Body
        if ($ir_mime) {
          if ($format == 'html') {
            $Mail_Mime->setHTMLBody($message);
          }
          else {
            $message = mb_convert_encoding($message, self::$SMTP_CHARSET, 'auto');
            $Mail_Mime->setTxtBody($message);
          }
        }
        else {
          $message = mb_convert_encoding($message, self::$SMTP_CHARSET, 'auto');
        }
        ///

        // Attachments
        if (!empty($attachments)) {
          for ($i = 0; $i < count($attachments); $i++) {

            $attachment = $attachments[$i];
            $mode       = $attachment['mode'];

            if ($mode == 'html_image') {
              $Mail_Mime->addHTMLImage(
                $attachment['data'],
                $attachment['type'],
                $attachment['name'],
                false
              );
            }
            elseif ($mode == 'attachment') {
              $Mail_Mime->addAttachment(
                $attachment['data'],
                $attachment['type'],
                $attachment['name'],
                false
              );
            }
          }
        }
        ///

        if ($ir_mime) {

          $message = $Mail_Mime->get([
            'head_charset' => self::$SMTP_CHARSET,
            'html_charset' => self::$SMTP_ENCODING,
            'text_charset' => self::$SMTP_CHARSET
          ]);

          $headers = $Mail_Mime->headers($headers);
        }









                $Mail = \Mail::factory('smtp', $params);
                $res  = $Mail->send($recipients, $headers, $message);

                mb_language($mb_language_tmp);
                mb_internal_encoding($mb_internal_encoding_tmp);

                $status = ($res == true) ? true : false;

                $ret = array(
                    'status' => $status,
                    'error'  => ($status ? null : $res)
                );

                return $ret;
            }
        }
    }

?>
