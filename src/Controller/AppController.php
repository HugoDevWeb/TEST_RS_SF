<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\RegistrationType;
use App\Form\SiteType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AppController
 * @package App\Controller
 * @Route("/app")
 */
class AppController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(PaginatorInterface $paginator, Request $request)
    {
        $this->isGranted("IS_AUTHENTICATED_FULLY");
        $em = $this->getDoctrine()->getManager();
        $data = $em->getRepository(Site::class)->findAll();

        $sites = $paginator->paginate(
            $data, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            5 // Nombre de résultats par page
        );

        return $this->render('app/index.html.twig', [
            'sites' => $sites,
        ]);
    }

    /**
     * @param Request $request
     * @param MailerInterface $mailer
     * @return RedirectResponse|Response
     * @throws TransportExceptionInterface
     * @Route("/create", name="create_site")
     */
    public function create(Request $request, MailerInterface $mailer)
    {
        $this->isGranted("IS_AUTHENTICATED_FULLY");

        $em = $this->getDoctrine()->getManager();
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (300 < $site->getStatus() && $site->getStatus() < 600) {
                $email = (new Email())
                    ->text("Bonjour, le site que vous venez de créer est désormais indisponible, veuillez modifier le statut du site pour confirmer la disponibilité de ce dernier")
                    ->to("admin@test.com")
                    ->from("siteAdministror@gmail.com");
                $mailer->send($email);
            }

            $em->persist($site);
            $em->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('app/create.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit_site")
     * @param $id
     * @param Request $request
     * @param MailerInterface $mailer
     * @return RedirectResponse|Response
     * @throws TransportExceptionInterface
     */
    public function edit($id, Request $request, MailerInterface $mailer)
    {
        $this->isGranted("IS_AUTHENTICATED_FULLY");

        $em = $this->getDoctrine()->getManager();
        /** @var Site $site */
        $site = $em->getRepository(Site::class)->find($id);

        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (300 < $site->getStatus() && $site->getStatus() < 600) {
                $email = (new Email())
                    ->text("Bonjour, le site que vous venez de modifier est désormais indisponible, veuillez modifier le statut du site pour confirmer la disponibilité de ce dernier")
                    ->to("admin@test.com")
                    ->from("siteAdministror@gmail.com");
                $mailer->send($email);
            }

            $em->persist($site);
            $em->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('app/edit.html.twig', [
            "form" => $form->createView()
        ]);
    }


    /**
     * @Route("/delete/{id}", name="delete_site")
     */
    public function delete($id, Request $request)
    {
        $this->isGranted("IS_FULLY_AUTHENTICATED");
        $em = $this->getDoctrine()->getManager();
        $submittedToken = $request->request->get('delete');
        $site = $em->getRepository(Site::class)->find($id);


        if ($this->isCsrfTokenValid('delete', $submittedToken)) {
            $em->remove($site);
            $em->flush();
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/email")
     * @param MailerInterface $mailer
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function sendEmail(MailerInterface $mailer)
    {
        $email = (new Email())
            ->text("BONJOUR")
        ->to("adresse@test.com")
        ->from("moi@gmail.com");

        //….
        $mailer->send($email);

        // …
        return new Response(
            'Email was sent'
        );
    }
}
