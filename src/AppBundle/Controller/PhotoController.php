<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Photo;
use AppBundle\Entity\Comment;
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
     * @Method("GET")
     */
    public function getPhotoAction(Request $request, Photo $photo)
    {
        // Authorization logic to be added here

        $uploadDir = $this->getParameter('photo_upload_dir');
        return new BinaryFileResponse($uploadDir . '/' . $photo->getFilename());
    }

    /**
     * @Route("/photo/thumb/{id}", requirements={
     *     "id": "\d+"
     * })
     * @Method("GET")
     */
    public function getPhotoThumbnailAction(Request $request, Photo $photo)
    {
        // Authorization logic to be added here

        $uploadDir = $this->getParameter('photo_upload_dir');
        return new BinaryFileResponse($uploadDir . '/' . $photo->getThumbFilename());
    }

    /**
     * @Route("/photo/resized/{id}", requirements={
     *     "id": "\d+"
     * })
     * @Method("GET")
     */
    public function getPhotoResizedAction(Request $request, Photo $photo)
    {
        // Authorization logic to be added here

        $uploadDir = $this->getParameter('photo_upload_dir');
        return new BinaryFileResponse($uploadDir . '/' . $photo->getResizedFilename());
    }

    /**
     * @Route("/photo")
     * @Method("POST")
     */
    public function postPhotoAction(Request $request)
    {
        $albumId = $request->get('albumId');
        $dateString = $request->get('date');
        $files = $request->files->get('photo', array());

        try {
            $date = new \DateTime($dateString);
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'message' => 'Invalid arguments',
                'list' => array('date: Unable to parse string.')
            ));
        }

        // Create an array if only one photo was uploaded
        if (!is_array($files)) {
            $files = array($files);
        }

        $em = $this->getDoctrine()->getEntityManager();
        $album = $em->getRepository('AppBundle:Album')->findOneById($albumId);

        if (null === $album) {
            $data = array(
                'message' => 'Invalid parameters',
                'list' => array('albumId: no album with such id.')
            );

            return new JsonResponse($data);
        }

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
     * @Method("DELETE")
     */
    public function deletePhotoAction(Request $request, Photo $photo)
    {
        $album = $photo->getAlbum();

        if (!in_array($this->getUser(), $album->getAuthors()->toArray())) {
            return new JsonResponse(array('message' => 'You are not allowed to delete this photo.'), 403);
        }

        $album->removePhoto($photo);

        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($photo);
        $em->persist($album);
        $em->flush();

        return new JsonResponse(array('message' => 'Photo deleted.'));
    }

    /**
     * @Route("/photo/{id}/comment", requirements={
     *     "id": "\d+"
     * })
     * @Method("POST")
     */
    public function commentPhotoAction(Request $request, Photo $photo)
    {
        $text = $request->get('text');

        $comment = new Comment();
        $comment->setText($text);
        $comment->setAuthor($this->getUser());
        $comment->setDate(new \DateTime());

        $photo->addComment($comment);

        $validator = $this->get('validator');
        $errors = $validator->validate($photo);

        if (count($errors) > 0) {
            return new JsonResponse(Util::violationListToJson($errors));
        }

        $em = $this->getDoctrine()->getEntityManager();
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
     * @Method("DELETE")
     */
    public function deleteAlbumCommentAction(Request $request, $photoId, $commentId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $photo = $em->getRepository('AppBundle:Photo')->findOneById($photoId);
        $comment = $em->getRepository('AppBundle:Comment')->findOneById($commentId);

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

        $photo->removeComment($comment);

        $em->remove($comment);
        $em->persist($photo);
        $em->flush();

        return new JsonResponse(array(
            'message' => 'Comment deleted.'
        ));
    }
}