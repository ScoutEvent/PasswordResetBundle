<?php

namespace ScoutEvent\PasswordResetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use ScoutEvent\PasswordResetBundle\Form\Type\ResetType;
use ScoutEvent\PasswordResetBundle\Form\Type\SendResetType;
use ScoutEvent\PasswordResetBundle\Entity\PasswordReset;

class DefaultController extends Controller
{
    public function sendAction(Request $request)
    {
        $form = $this->createForm(new SendResetType(), null, array(
            'action' => $this->generateUrl('scout_password_send_reset')
        ));
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $email = $form->get('email')->getData();
            
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('ScoutEventBaseBundle:User')->findOneBy(array('email' => $email));
            
            if (!$user)
            {
                return $this->render('ScoutEventPasswordResetBundle:Default:resetComplete.html.twig');
            }
            
            $reset = $em->getRepository('ScoutEventPasswordResetBundle:PasswordReset')->findOneBy(array('user' => $user));
            if (!$reset) {
                // Create new password reset request
                $reset = new PasswordReset($user);
                $em->persist($reset);
                $em->flush();
            }
        
            // Send password reset email
            $message = \Swift_Message::newInstance();
            $message->setFrom($this->container->getParameter('mailer_from'));
            $message->setSubject('Scout event management password reset');
        
            $resetLink = $this->generateUrl('scout_password_reset', array(
                'token' => $reset->getToken()
            ));
            $resetLink = $this->getRequest()->getUriForPath($resetLink);
            $message->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'ScoutEventPasswordResetBundle:Default:reset.txt.twig',
                        array(
                            'resetLink' => $resetLink
                        )
                    )
                );
            $this->get('mailer')->send($message);

            return $this->render('ScoutEventPasswordResetBundle:Default:resetComplete.html.twig');
        }
        
        return $this->render('ScoutEventPasswordResetBundle:Default:sendReset.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function resetAction(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();
        $resetToken = $em
            ->getRepository('ScoutEventPasswordResetBundle:PasswordReset')
            ->findOneBy(array('token' => $token));
        
        if ($resetToken == null)
        {
            // Invalid token
            return $this->redirect($this->generateUrl('scout_base_app_list'));
        }
    
        $form = $this->createForm(new ResetType(), null, array(
            'action' => $this->generateUrl('scout_password_reset', array("token" => $token))
        ));
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $user = $resetToken->getUser();
            $user->setRawPassword($form->get('password')->getData(), $this->get('security.encoder_factory'));
            $em->remove($resetToken);
            $em->flush();
        
            $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);

            return $this->redirect($this->generateUrl('scout_base_app_list'));
        }
        
        return $this->render('ScoutEventPasswordResetBundle:Default:reset.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
