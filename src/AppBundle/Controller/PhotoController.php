<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Photo;
use AppBundle\Entity\PhotoComment;
use AppBundle\Util\ImagickPhotoResizer;
use AppBundle\Util\Util;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PhotoController extends Controller
{
    /**
     * @Route("/photo/{id}", requirements={
     *     "id": "\d+"
     * })
     * @Method({"GET", "OPTIONS"})
     */
    public function getPhotoAction(Request $request, Photo $photo)
    {
        $this->denyAccessUnlessGranted('view', $photo, 'You are not allowed to view this photo.');

        $uploadDir = $this->getParameter('photo_upload_dir');
        return new BinaryFileResponse($uploadDir . '/' . $photo->getFilename());
    }

    /**
     * @Route("/photo/{id}/thumb", requirements={
     *     "id": "\d+"
     * })
     * @Method({"GET", "OPTIONS"})
     */
    public function getPhotoThumbnailAction(Request $request, Photo $photo)
    {
        $this->denyAccessUnlessGranted('view', $photo, 'You are not allowed to view this photo.');

        $uploadDir = $this->getParameter('photo_upload_dir');
        return new BinaryFileResponse($uploadDir . '/' . $photo->getThumbFilename());
    }

    /**
     * @Route("/photo/{id}/resized", requirements={
     *     "id": "\d+"
     * })
     * @Method({"GET", "OPTIONS"})
     */
    public function getPhotoResizedAction(Request $request, Photo $photo)
    {
        $this->denyAccessUnlessGranted('view', $photo, 'You are not allowed to view this photo.');

        $uploadDir = $this->getParameter('photo_upload_dir');
        return new BinaryFileResponse($uploadDir . '/' . $photo->getResizedFilename());
    }

    /**
     * @Route("/photo/{id}/cover", requirements={
     *     "id": "\d+"
     * })
     * @Method({"GET", "OPTIONS"})
     */
    public function getPhotoCoverAction(Request $request, Photo $photo)
    {
        $this->denyAccessUnlessGranted('view', $photo, 'You are not allowed to view this photo.');

        $uploadDir = $this->getParameter('photo_upload_dir');
        return new BinaryFileResponse($uploadDir . '/' . $photo->getCoverFilename());
    }

    /**
     * @Route("/photo")
     * @Method({"POST", "OPTIONS"})
     */
    public function postPhotoAction(Request $request)
    {
        $albumId = $request->request->get('albumId');
        $dateString = $request->request->get('date');
        $files = $request->files->get('photo', array());

        try {
            $date = new \DateTime($dateString);
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'message' => 'Invalid arguments',
                'list' => array('date: Unable to parse string.')
            ), 422);
        }

        // Create an array if only one photo was uploaded
        if (!is_array($files)) {
            $files = array($files);
        }

        $em = $this->getDoctrine()->getManager();
        $album = $em->getRepository('AppBundle:Album')->findOneById($albumId);

        if (null === $album) {
            $data = array(
                'message' => 'Invalid parameters',
                'list' => array('albumId: no album with such id.')
            );

            return new JsonResponse($data, 422);
        }

        $this->denyAccessUnlessGranted('edit', $album, 'You cannot add photos to this album.');

        foreach ($files as $file) {
            $photo = new Photo();
            $photo->setDate($date)
                ->setUploadDate(new \DateTime())
                ->setAuthor($this->getUser())
                ->setFile($file);

            $album->addPhoto($photo);
        }

        $validator = $this->get('validator');
        $errors = $validator->validate($album);

        if (count($errors) > 0) {
            return new JsonResponse(Util::violationListToJson($errors), 422);
        }

        $em->persist($album);
        $em->flush();

        return new JsonResponse(array('message' => 'Photo added.'));
    }

    /**
     * @Route("/photo/{id}", requirements={
     *     "id": "\d+"
     * })
     * @Method({"DELETE", "OPTIONS"})
     */
    public function deletePhotoAction(Request $request, Photo $photo)
    {
        $this->denyAccessUnlessGranted('delete', $photo, 'You are not allowed to delete this photo.');

        $album = $photo->getAlbum();
        $album->removePhoto($photo);

        $em = $this->getDoctrine()->getManager();
        $em->remove($photo);
        $em->persist($album);
        $em->flush();

        return new JsonResponse(array('message' => 'Photo deleted.'));
    }

    /**
     * @Route("/photo/{id}/comment", requirements={
     *     "id": "\d+"
     * })
     * @Method({"POST", "OPTIONS"})
     */
    public function commentPhotoAction(Request $request, Photo $photo)
    {
        $this->denyAccessUnlessGranted('comment', $photo, 'You are not allowed to comment this photo.');

        $text = $request->get('text');

        $comment = new PhotoComment();
        $comment->setText($text);
        $comment->setAuthor($this->getUser());
        $comment->setDate(new \DateTime());

        $photo->addComment($comment);

        $validator = $this->get('validator');
        $errors = $validator->validate($photo);

        if (count($errors) > 0) {
            return new JsonResponse(Util::violationListToJson($errors), 422);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->persist($photo);
        $em->flush();

        return new JsonResponse($photo->toJson());
    }

    /**
     * @Route("/photo/{photoId}/comment/{commentId}", requirements={
     *     "photoId": "\d+",
     *     "commentId": "\d+"
     * })
     * @Method({"DELETE", "OPTIONS"})
     */
    public function deletePhotoCommentAction(Request $request, $photoId, $commentId)
    {
        $em = $this->getDoctrine()->getManager();
        $photo = $em->getRepository('AppBundle:Photo')->findOneById($photoId);
        $comment = $em->getRepository('AppBundle:PhotoComment')->findOneById($commentId);

        if (null === $photo) {
            return new JsonResponse(array(
                'message' => 'No photo with such id.'
            ), 404);
        }

        if (null === $comment OR !$photo->getComments()->contains($comment)) {
            return new JsonResponse(array(
                'message' => 'This photo does not contain a comment with such id.'
            ), 404);
        }

        $this->denyAccessUnlessGranted('delete', $comment, 'You are not allowed to delete this comment.');

        $photo->removeComment($comment);
        $em->remove($comment);
        $em->flush();

        return new JsonResponse(array(
            'message' => 'Comment deleted.'
        ));
    }
}
