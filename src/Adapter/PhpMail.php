<?php


namespace depa\FormularHandlerMiddleware\Adapter;

use depa\FormularHandlerMiddleware\AbstractAdapter;

/**
 * Class PhpMail
 * @package depa\FormularHandlerMiddleware\Adapter
 */
class PhpMail extends AbstractAdapter
{
    use ReplyToTrait;

    /**
     * @return mixed|void
     */
    public function handleData()
    {
        $formData = $this->validFields;
        $mailData = $this->config['adapter']['phpmail'];

        try {
            $loader = new \Twig\Loader\ArrayLoader([
                'mail.html' => $mailData['template'],
            ]);
            $twig = new \Twig\Environment($loader);

            $mailMessage = $twig->render('mail.html', $formData);

            $replyTo = $this->replyTo($mailData);
            if (!$this->errorStatus) {
                $this->sendMail($mailData, $mailMessage, $replyTo);
            }

        } catch (\Exception $e) {
            parent::setError('Error occourd in: ' . $e->getFile() . ' on line: ' . $e->getLine() . ' with message: ' . $e->getMessage());
        }
    }

    /**
     *
     * Verschickt eine Mail
     *
     * @param $mailData
     * @param $mailMessage
     */
    private function sendMail($mailData, $mailMessage, $replyTo)
    {
        $loader = new \Twig\Loader\ArrayLoader([
            'mail.html' => $mailData['subject'],
        ]);
        $twig = new \Twig\Environment($loader);

        $replacements = [];
        foreach ($this->validFields as $key => $value) {
            $replacements[$key] = $value;
        }

        $mailSubject = $twig->render('mail.html', $replacements);

        $header = array(
            'From' => $mailData['sender'],
        );
        if (!is_null($replyTo)) {
            $header['Reply-to'] = $replyTo;
        }

        foreach ($mailData['recipients'] as $recipient) {

            mail(
                $recipient,
                $mailSubject,
                $mailMessage,
                $header
            );
        }
    }

    /**
     * Prüft die übergebene Config (beinhaltet den Adapter) nach den benötigten Werten.
     * @param $config
     * @return |null
     */
    protected function checkConfig($config)
    {
        if (!isset($config['adapter']) || is_null($config['adapter']) || !is_array($config['adapter'])) {
            parent::setError('The adapter was not found in config!');
            return null;
        }
        if (!isset($config['adapter']['phpmail']) || is_null($config['adapter']['phpmail']) || !is_array($config['adapter']['phpmail'])) {
            parent::setError('There is no mail-config inside the adapter!');
            return null;
        }
        $mailConfig = $config['adapter']['phpmail'];

        if (!isset($mailConfig['email_transfer']) || is_null($mailConfig['email_transfer']) || !is_array($mailConfig['email_transfer'])) {
            //Fehler: keine gültige config für das versenden von mails
            parent::setError('There is no email_transfer defined inside the mail-adapter config!');
            return null;
        }
        $mailTransfer = $mailConfig['email_transfer'];

        if (!isset($mailTransfer['method']) || is_null($mailTransfer['method']) || is_array($mailTransfer['method'])) {
            //Fehler: keine gültige definition für Transfer-Methode
            parent::setError('There is no method defined inside the email_transfer config!');
            return null;
        }

        if (!isset($mailTransfer['config']) || is_null($mailTransfer['config']) || !is_array($mailTransfer['config'])) {
            //Fehler: ungültige Config für Transfer-Methode
            parent::setError('There is no mail-config defined inside the email_transfer config!');
            return null;
        }

        if (!isset($mailConfig['recipients']) || is_null($mailConfig['recipients']) || !is_array($mailConfig['recipients'])) {
            //Fehler: ungültige definition von recipients
            parent::setError('recipients-arraay is not properly defined in email_transfer-config!');
            return null;
        }

        if (!isset($mailConfig['subject']) || is_null($mailConfig['subject']) || !is_string($mailConfig['subject'])) {
            //Fehler: ungültige definition von Subject
            parent::setError('There is no subject defined inside the transfer_config!');
            return null;
        }

        if (!isset($mailConfig['sender']) || is_null($mailConfig['sender']) || !is_string($mailConfig['sender'])) {
            //Fehler: ungültige definition von Subject
            parent::setError('There is no sender defined inside the transfer_config!');
            return null;
        }

        if (!isset($mailConfig['senderName']) || is_null($mailConfig['senderName']) || !is_string($mailConfig['senderName'])) {
            //Fehler: ungültige definition von Subject
            parent::setError('There is no senderName defined inside the transfer_config!');
            return null;
        }

        return $config;
    }
}