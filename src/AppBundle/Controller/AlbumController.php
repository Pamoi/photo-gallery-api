<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Album;
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
}