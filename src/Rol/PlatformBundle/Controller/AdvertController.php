<?php

namespace Rol\PlatformBundle\Controller;

use Rol\PlatformBundle\Entity\Advert;
use Rol\PlatformBundle\Entity\Image;
use Rol\PlatformBundle\Entity\Application;
use Rol\PlatformBundle\Form\AdvertType;
use Rol\PlatformBundle\Form\AdvertEditType;
use Rol\PlatformBundle\Form\ApplicationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdvertController extends Controller
{
    public function indexAction($page)
    {
       
        if ($page < 1) {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }
        $nbPerPage = 4;
        // Pour récupérer la liste de toutes les annonces : on utilise findAll()
        $listAdverts = $this->getDoctrine()
            ->getManager()
            ->getRepository('RolPlatformBundle:Advert')
            ->getAdverts($page, $nbPerPage)
            ;

        // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
        $nbPages = ceil(count($listAdverts)/$nbPerPage);

        // Si la page n'existe pas, on retourne une 404
        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        // On donne toutes les informations nécessaires à la vue
        return $this->render('RolPlatformBundle:Advert:index.html.twig', array(
            'listAdverts' => $listAdverts,
            'nbPages'     => $nbPages,
            'page'        => $page
        ));
    }
    
    public function viewAction($id)
    {
       
        $em = $this->getDoctrine()->getManager();

        // On récupère l'entité correspondante à l'id $id
        $Advert = $em->getRepository('RolPlatformBundle:Advert')->find($id);

        // $Advert est donc une instance de Rol\PlatformBundle\Entity\Advert
        // ou null si l'id $id  n'existe pas, d'où ce if :
        if (null === $Advert) {
            throw new NotFoundHttpException("Le sujet d'id ".$id." n'existe pas.");
        }
        // On récupère la liste des candidatures de cette annonce
        $listApplications = $em
            ->getRepository('RolPlatformBundle:Application')
            ->findBy(array('Advert' => $Advert))
            ;

        return $this->render('RolPlatformBundle:Advert:view.html.twig', array('Advert' => $Advert, 'listApplications' => $listApplications));
    }
    
    /**
    * @Security("has_role('ROLE_AUTEUR')")
    */
    public function addAction(Request $request)
    {
        // On vérifie que l'utilisateur dispose bien du rôle ROLE_AUTEUR
        if (!$this->get('security.context')->isGranted('ROLE_AUTEUR')) {
            // Sinon on déclenche une exception « Accès interdit »
            throw new AccessDeniedException('Accès limité aux auteurs.');
        }
        $user = $this->get('security.context')->getToken()->getUser();
        $Advert = new Advert();
        $Advert->setAuthor($user);
        $form = $this->get('form.factory')->create(new AdvertType(), $Advert);

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($Advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Sujet bien enregistrée.');

            return $this->redirect($this->generateUrl('rol_platform_view', array('id' => $Advert->getId())));
        }

        return $this->render('RolPlatformBundle:Advert:add.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $Advert = $em->getRepository('RolPlatformBundle:Advert')->find($id);

        if (null === $Advert) {
            throw new NotFoundHttpException("Le sujet d'id ".$id." n'existe pas.");
        }

        $form = $this->createForm(new AdvertEditType(), $Advert);

        if ($form->handleRequest($request)->isValid()) {
            // Inutile de persister ici, Doctrine connait déjà notre annonce
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Message bien modifiée.');

            return $this->redirect($this->generateUrl('rol_platform_view', array('id' => $Advert->getId())));
        }

        return $this->render('RolPlatformBundle:Advert:edit.html.twig', array(
            'form'   => $form->createView(),
            'Advert' => $Advert
        ));
    }
    
    public function deleteAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $Advert = $em->getRepository('RolPlatformBundle:Advert')->find($id);

        if (null === $Advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'annonce contre cette faille
        $form = $this->createFormBuilder()->getForm();

        if ($form->handleRequest($request)->isValid()) {
            $em->remove($Advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");

            return $this->redirect($this->generateUrl('rol_platform_home'));
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('RolPlatformBundle:Advert:delete.html.twig', array(
            'Advert' => $Advert,
            'form'   => $form->createView()
        ));
    }
    
    public function menuAction($limit)
    {
        $listAdverts = $this->getDoctrine()
            ->getManager()
            ->getRepository('RolPlatformBundle:Advert')
            ->findBy(
            array(),                 // Pas de critère
            array('date' => 'desc'), // On trie par date décroissante
            $limit,                  // On sélectionne $limit annonces
            0                        // À partir du premier
        );

        return $this->render('RolPlatformBundle:Advert:menu.html.twig', array(
            'listAdverts' => $listAdverts
        ));
    }
    
    public function applicationAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $Advert = $em->getRepository('RolPlatformBundle:Advert')->find($id);
        
        $user = $this->get('security.context')->getToken()->getUser();
        
        $application = new Application();
        $application->setAuthor($user);
        $application->setAdvert($Advert);
        $form = $this->get('form.factory')->create(new ApplicationType(), $application);

        if ($form->handleRequest($request)->isValid()) {
            //$Advert->getImage()->upload();
            $em = $this->getDoctrine()->getManager();
            $em->persist($application);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Message bien enregistrée.');

            return $this->redirect($this->generateUrl('rol_platform_view', array('id' => $Advert->getId())));
        }

        return $this->render('RolPlatformBundle:Advert:application.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
