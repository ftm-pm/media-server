<?php

namespace App\Handler;

use App\Entity\User;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * Class UserHandler
 * @package App\Handler
 */
class UserHandler
{
    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * UserHandler constructor.
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(EncoderFactoryInterface $encoderFactory, \Swift_Mailer $mailer, RegistryInterface $doctrine)
    {
        $this->encoderFactory = $encoderFactory;
        $this->mailer = $mailer;
        $this->doctrine = $doctrine;
    }

    /**
     * @param User $user
     */
    public function hashPassword(User $user)
    {
        $plainPassword = $user->getPassword();
        $user->setPassword(null);
        if (0 === strlen($plainPassword)) {
            return;
        }

        $encoder = $this->encoderFactory->getEncoder($user);

        if ($encoder instanceof BCryptPasswordEncoder) {
            $user->setSalt(null);
        } else {
            $salt = $this->generateSalt();
            $user->setSalt($salt);
        }

        $hashedPassword = $encoder->encodePassword($plainPassword, $user->getSalt());
        $user->setPassword($hashedPassword);
        $user->eraseCredentials();
    }

    /**
     * @param array $data
     * @return array
     */
    public function register($data = []) {
        $em = $this->doctrine->getManager();
        $repository = $em->getRepository('App:User');
        if( isset($data['username']) && isset($data['email']) && isset($data['password'])
            && !$repository->loadUserByUsername($data['email'], $data['username'])) {

            $user = new User();
            $user->setUsername($data['username'])
                ->setEmail($data['email'])
                ->setPassword($data['password'])
                ->setEnabled(false);
            $em->persist($user);
            $em->flush();
            $code = 200;
            $message = 'Messege send on your email.';
        } else {
            $code = 404;
            $message =  'Provided username or email already in use.';
        }

        return [
            'message' => $message,
            'code' => $code
        ];
    }
    /**
     * @param User $user
     */
    public function sendActivateMessage(User $user) {
        $token = $this->generateToken();

        $user->setConfirmationToken($token);
        $message = (new \Swift_Message('Registration confirmation on ' . getenv('APP_NAME')))
            ->setFrom(getenv('APP_MAIL_DEFAULT'))
            ->setTo($user->getEmail())
            ->setBody(sprintf('<a href="%s/api/token/confirmation?token=%s" target="_blank">Confirmation link</a>',
                getenv('APP_HOST'), $token), 'text/html');

        $this->mailer->send($message);
    }

    /**
     * @param $token
     * @return bool
     */
    public function confirmationEmail($token) {
        $em = $this->doctrine->getManager();
        $repository = $em->getRepository('App:User');
        $user = $repository->findOneBy([
            'confirmationToken' => $token
        ]);
        if ($user) {
            $user->setEnabled(true);
            $user->setConfirmationToken(null);
        }
        $em->flush();

        return $user !== null;
    }

    /**
     * @return string
     */
    private function generateToken() {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /**
     * @return string
     */
    private function generateSalt() {
        return rtrim(str_replace('+', '.', base64_encode(random_bytes(32))), '=');
    }
}