<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Album;
use AppBundle\Entity\Comment;
use AppBundle\Util\Util;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AlbumController extends Controller
{
    /**
     * @Route("/album/{page}", defaults={"page": 1}, requirements={
     *     "page": "\d+"
     * })
     * @Method("GET")
     */
    public function getAlbumAction(Request $request, $page)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Album');

        $albums = $repo->loadPage($page, 5);
        $data = array_map(function($a) { return $a->toJson(); }, $albums);

        return new JsonResponse($data);
    }

    /**
     * @Route("/album")
     * @Method("POST")
     */
    public function postAlbumAction(Request $request)
    {
        $title = $request->get('title');
        $description = $request->get('description');
        $dateString = $request->get('date');

        try {
            $date = new \DateTime($dateString);
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'message' => 'Invalid arguments',
                'list' => array('date: Unable to parse string.')
            ));
        }

        $album = new Album();

        $album->setTitle($title);
        $album->setDescription($description);
        $album->setDate($date);
        $album->setCreationDate(new \DateTime());
        $album->addAuthor($this->getUser());

        $validator = $this->get('validator');
        $errors = $validator->validate($album);

        if (count($errors) > 0) {
            return new JsonResponse(Util::violationListToJson($errors), 422);
        }

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($album);
        $em->flush();

        return new JsonResponse($album->toJson());
    }

    /**
     * @Route("/album/{id}", requirements={
     *     "id": "\d+"
     * })
     * @Method("DELETE")
     */
    public function deleteAlbumAction(Request $request, Album $album)
    {
        if (!in_array($this->getUser(), $album->getAuthors()->toArray())) {
            return new JsonResponse(array('message' => 'You are not allowed to delete this album.'), 403);
        }

        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($album);
        $em->flush();

        return new JsonResponse(array('message' => 'Album deleted.'));
    }

    /**
     * @Route("/album/{id}/comment", requirements={
     *     "id": "\d+"
     * })
     * @Method("POST")
     */
    public function commentAlbumAction(Request $request, Album $album)
    {
        $text = $request->get('text');

        $comment = new Comment();
        $comment->setText($text);
        $comment->setAuthor($this->getUser());
        $comment->setDate(new \DateTime());

        $album->addComment($comment);

        $validator = $this->get('validator');
        $errors = $validator->validate($album);

        if (count($errors) > 0) {
            return new JsonResponse(Util::violationListToJson($errors));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($comment);
        $em->persist($album);
        $em->flush();

        return new JsonResponse($album->toJson());
    }

    /**
     * @Route("/album/{albumId}/comment/{commentId}", requirements={
     *     "albumId": "\d+",
     *     "commentId": "\d+"
     * })
     * @Method("DELETE")
     */
    public function deleteAlbumCommentAction(Request $request, $albumId, $commentId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $album = $em->getRepository('AppBundle:Album')->findOneById($albumId);
        $comment = $em->getRepository('AppBundle:Comment')->findOneById($commentId);

        if (null === $album) {
            return new JsonResponse(array(
                'message' => 'No album with such id.'
            ), 404);
        }

        if (null === $comment OR !$album->getComments()->contains($comment)) {
            return new JsonResponse(array(
                'message' => 'This album does not contain a comment with such id.'
            ), 404);
        }

        $album->removeComment($comment);

        $em->remove($comment);
        $em->persist($album);
        $em->flush();

        return new JsonResponse(array(
            'message' => 'Comment deleted.'
        ));
    }
}