<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Annonces;
use App\Form\AnnoncesType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Images;
use App\Repository\AnnoncesRepository;

class AnnoncesController extends AbstractController
{
    
        /**
         * @Route("/", name="annonces_index", methods={"GET"})
         */
        public function index(AnnoncesRepository $annoncesRepository): Response
        {
            return $this->render('annonces/index.html.twig', [
                'annonces' => $annoncesRepository->findAll(),
            ]);
        }

        /**
         * @Route("/new", name="annonces_new", methods={"GET","POST"})
         */
        public function new(Request $request): Response
        {
            $annonce = new Annonces();
            $form = $this->createForm(AnnoncesType::class, $annonce);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //recuperation des images transmises
                $images = $form->get('images')->getData();
                // Boucle sur les images
                foreach ($images as $image) {
                    // on genere un nouveau nom de fichier unique avec md5. guessExtension recupere l'extension du fichier
                    $fichier = md5(uniqid()) . '.' . $image->guessExtension();
                    //On copie le fichier dans le dossier upload
                    $image->move(
                        $this->getParameter('upload_directory'),
                        $fichier
                    );

                    // on stocke l'image dans la bdd (son nom)
                    $img = new Images();
                    $img->setName($fichier);
                    $annonce->addImage($img);
                }
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($annonce);
                $entityManager->flush();

                return $this->redirectToRoute('annonces_index');
            }

            return $this->render('annonces/new.html.twig', [
                'annonce' => $annonce,
                'form' => $form->createView(),
            ]);
        }
        /**
         * @Route("/{id}", name="annonces_show", methods={"GET"})
         */
        public function show(Annonces $annonce): Response
        {
            return $this->render('annonces/show.html.twig', [
                'annonce' => $annonce,
            ]);
        }

}
