<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoType;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VideoController extends AbstractController
{
    /**
     * @Route("/video", name="video")
     */
    public function index(Request $request, VideoRepository $videoRepository)
    {
        $video = new Video();
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $video->setUser($this->getUser());
            $entityManager->persist($video);
            $entityManager->flush();
            $this->addFlash('success', 'Votre video a bien ete mise en ligne !');
            return $this->redirectToRoute('home');
        }
        $videos = $videoRepository->findAll();

        return $this->render('video/index.html.twig', array(
            'videos' => $videos,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/video/{id}", name="detail_video")
     * @ParamConverter("video", options={"mapping"={"id"="id"}})
     */
    public function video(Video $video)
    {
        return $this->render('video/detail.html.twig', array(
            'video' => $video,
        ));
    }

    /**
     * @Route("/video/remove/{id}", name="remove_video")
     * @ParamConverter("video", options={"mapping"={"id"="id"}})
     */
    public function remove(Video $video, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($video);
        $entityManager->flush();
        $this->addFlash('notice', 'Element supprimer !');
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/video/edit/{id}", name="edit_video")
     */
    public function edit(Request $request, Video $video = null)
    {
        if (!$video) {
            $video = new video();
        }
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($video);
            $entityManager->flush();
            $this->addFlash('success', 'Modification enregistrer !');
            return $this->redirectToRoute('detail_video', ['id' => $video->getId()]);
        }


        return $this->render('video/modifier.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/video/list/{id}", name="liste_video")
     */
    public function liste($id)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if ($user === "anon.") {
            $videos = $this->getDoctrine()->getRepository(Video::class)->findBy(array('user' => $id, 'published' => 1));
        } else {
            if ($user->getid($id) == $id) {
                $videos = $this->getDoctrine()->getRepository(Video::class)->findBy(array('user' => $id));
            } else {
                $videos = $this->getDoctrine()->getRepository(Video::class)->findBy(array('user' => $id, 'published' => 1));
            }
        }

        return $this->render('video/user_article.html.twig', [
            'video' => $videos
        ]);
    }
}
