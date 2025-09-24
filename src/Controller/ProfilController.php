<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profil')]
final class ProfilController extends AbstractController
{
    #[Route('/')]
    public function index(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $participant = $this->getUser();
        $baseUrl = $request->getSchemeAndHttpHost();

        $form = $this->createForm(ProfilType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['photo']->getData();

            if ($participant->getPhoto()) {
                $oldPath = __DIR__ . '/../../public/photo/' . $participant->getPhoto();
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

            // Déplacement du fichier
            $file->move(
                __DIR__ . '/../../public/photo',
                $newFilename
            );

            $participant->setPhoto($newFilename);

            /*
             *             $participant.getPhoto() ? new File($this->getUploadRootDir() . '/' . $this->documentPath) : null;

            $file = $form['photo']->getData();
            $file->move(__DIR__."../../public/photo", $participant->getNom().$participant->getPrenom());
*/

            $entityManager->persist($participant);
            $entityManager->flush();
            $this->addFlash("success", "Modifications prises en compte");
            return $this->redirectToRoute('app_profil_index');
        }

        return $this->render('profil/profil.html.twig', [
            'form' => $form,
            'photo' => $baseUrl . '/photo/'.$participant->getPhoto(),
        ]);
    }


    #[Route('/consulter/{id}')]
    public function consulter(Request $request, ParticipantRepository $participantRepository, int $id): Response
    {
        /** @var Participant $participant */
        $participant = $participantRepository->find($id);
        $baseUrl = $request->getSchemeAndHttpHost();

        return $this->render('profil/consulter.html.twig', [
            'participant' => $participant,
            'photo' => $baseUrl . '/photo/'.$participant->getPhoto(),
        ]);
    }
}
