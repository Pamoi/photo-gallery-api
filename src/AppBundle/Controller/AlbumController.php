<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Album;
use AppBundle\Entity\AlbumComment;
use AppBundle\Util\Util;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AlbumController extends Controller
{
    private static $NEW_ALBUMS_LIMIT = 25;
    private static $OLD_ALBUMS_LIMIT = 5;
    private static $ALBUMS_PER_PAGE = 5;

    /**
     * @Route("/album/{id}", requirements={
     *     "id": "\d+"
     * })
     * @Method({"GET", "OPTIONS"})
     */
    public function getAlbumAction(Request $request, Album $album)
    {
        $this->denyAccessUnlessGranted('view', $album);

        return new JsonResponse($album->toJson());
    }

    /**
     * @Route("/album/random")
     * @Method({"GET", "OPTIONS"})
     */
    public function getRandomAlbumAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Album');
        $maxIterations = 20;
        $i = 0;

        do {
            $album = $repo->getRandomAlbum();
            $i++;
        } while (!$this->isGranted('view', $album) && $i <= $maxIterations);

        if ($album) {
            return new JsonResponse($album->toJson());
        } else {
            return new JsonResponse(array('message' => 'No album found'), 404);
        }
    }

    /**
     * @Route("/album/list")
     * @Method({"GET", "OPTIONS"})
     */
    public function getAlbumsByDateAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Album');

        $after = true;
        $dateString = $request->query->get('after', null);

        if ($dateString === null) {
            $after = false;
            $dateString = $request->query->get('before', null);
        }

        try {
            $date = new \DateTime($dateString);
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'message' => 'Invalid arguments.',
                'list' => array('date: Unable to parse string.')
            ), 422);
        }

        if ($after) {
            $albums = $repo->afterDate($date, AlbumController::$NEW_ALBUMS_LIMIT);
        } else {
            $albums = $repo->beforeDate($date, AlbumController::$OLD_ALBUMS_LIMIT);

            if (count($albums) === 0) {
                throw new NotFoundHttpException('There is no album before this date.');
            }
        }

        $albums = array_filter($albums, $this->getIsGrantedFilter('view'));
        $data = array_map($this->getJsonMapper(), $albums);

        return new JsonResponse($data);
    }

    /**
     * @Route("/album/list/{page}", requirements={
     *     "page": "\d+"
     * })
     * @Method({"GET", "OPTIONS"})
     */
    public function getAlbumListAction(Request $request, $page)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Album');

        $albums = $repo->loadPage($page, AlbumController::$ALBUMS_PER_PAGE);

        if (count($albums) == 0) {
            throw new NotFoundHttpException('There is no album at this page.');
        }

        $albums = array_filter($albums, $this->getIsGrantedFilter('view'));
        $data = array_map($this->getJsonMapper(), $albums);

        return new JsonResponse($data);
    }

    /**
     * @Route("/album/{id}/downloadToken", requirements={
     *     "id": "\d+"
     * })
     * @Method({"GET", "OPTIONS"})
     */
    public function getDownloadTokenAction(Request $request, Album $album)
    {
        $this->denyAccessUnlessGranted('view', $album);

        $secret = $uploadDir = $this->getParameter('secret') . '54 90df2!!fh++ gGZ)=';
        $date = new \DateTime();
        $time = $date->format('d-m-Y H:i');
        $token = hash('sha256', $secret . $time . $album->getId());

        return new JsonResponse(array(
          'token' => $token
        ));
    }

    /**
     * @Route("/album/{id}/download", requirements={
     *     "id": "\d+"
     * })
     * @Method({"GET", "OPTIONS"})
     */
    public function downloadAlbumAction(Request $request, Album $album)
    {
        // Verify token
        $secret = $uploadDir = $this->getParameter('secret') . '54 90df2!!fh++ gGZ)=';
        $date = new \DateTime();
        $time = $date->format('d-m-Y H:i');
        $correct = hash('sha256', $secret . $time . $album->getId());
        $token = $request->query->get('token');

        if ($token === null) {
          $token = '';
        }

        if (!hash_equals($correct, $token)) {
          return new JsonResponse(array('message' => 'Invalid token.'), 403);
        }
        
        if ($album->getPhotos()->count() == 0) {
        	return new JsonResponse(array(
        			'message' => 'This album does not contain any photo.'
        	), 204);
        }

        $uploadDir = $this->getParameter('photo_upload_dir');
        $filename = $uploadDir . '/' . $album->getArchiveName();
        
        if (!file_exists($filename)) {
        	throw new \Exception('Archive file ' . $filename . ' does not exist.');
        }

        $response = new BinaryFileResponse($filename);
        $response->headers->set('Content-disposition', 'attachment;filename="' . $album->getTitle() . '.zip"');

        return $response;
    }

    /**
     * @Route("/album/search/{title}")
     * @Method({"GET", "OPTIONS"})
     */
    public function findAlbumAction(Request $request, $title)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Album');

        $albums = $repo->createQueryBuilder('a')
            ->where('a.title LIKE :title')
            ->setParameter('title', '%' . $title . '%')
            ->getQuery()
            ->getResult();

        $albums = array_filter($albums, $this->getIsGrantedFilter('view'));
        $data = array_map($this->getJsonMapper(), $albums);

        return new JsonResponse($data);
    }

    /**
     * @Route("/album")
     * @Method({"POST", "OPTIONS"})
     */
    public function postAlbumAction(Request $request)
    {
        $title = $request->request->get('title');
        $description = $request->request->get('description');
        $dateString = $request->request->get('date');
        $authorsIds = explode(',', $request->request->get('authorsIds'));

        try {
            $date = new \DateTime($dateString);
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'message' => 'Invalid arguments.',
                'list' => array('date: Unable to parse string.')
            ), 422);
        }

        $em = $this->getDoctrine()->getManager();
        $album = new Album();

        $album->setTitle($title);
        $album->setDescription($description !== null ? $description : '');
        $album->setDate($date);
        $album->setCreationDate(new \DateTime());
        $album->addAuthor($this->getUser());

        $authors = $em->getRepository('AppBundle:User')->findById($authorsIds);

        foreach ($authors as $author) {
            $album->addAuthor($author);
        }

        $validator = $this->get('validator');
        $errors = $validator->validate($album);

        if (count($errors) > 0) {
            return new JsonResponse(Util::violationListToJson($errors), 422);
        }

        $em->persist($album);
        $em->flush();

        return new JsonResponse($album->toJson());
    }

    /**
     * @Route("/album/{id}", requirements={
     *     "id": "\d+"
     * })
     * @Method({"POST", "OPTIONS"})
     *
     * This controller uses POST method as PHP does not get form data from PUT methods.
     */
    public function putAlbumAction(Request $request, Album $album)
    {
        $this->denyAccessUnlessGranted('edit', $album, 'You are not allowed to edit this album.');

        $title = $request->request->get('title');
        $description = $request->request->get('description');
        $dateString = $request->request->get('date');
        $authorsIds = explode(',', $request->request->get('authorsIds'));

        try {
            $date = new \DateTime($dateString);
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'message' => 'Invalid arguments.',
                'list' => array('date: Unable to parse string.')
            ), 422);
        }

        $title === null ? : $album->setTitle($title);
        $description === null ? : $album->setDescription($description);
        $dateString === null ? : $album->setDate($date);

        $em = $this->getDoctrine()->getManager();
        $authors = $em->getRepository('AppBundle:User')->findById($authorsIds);

        foreach ($authors as $author) {
            if (!$album->getAuthors()->contains($author)) {
                $album->addAuthor($author);
            }
        }

        $validator = $this->get('validator');
        $errors = $validator->validate($album);

        if (count($errors) > 0) {
            return new JsonResponse(Util::violationListToJson($errors), 422);
        }

        $em->persist($album);
        $em->flush();

        return new JsonResponse($album->toJson());
    }

    /**
     * @Route("/album/{id}", requirements={
     *     "id": "\d+"
     * })
     * @Method({"DELETE", "OPTIONS"})
     */
    public function deleteAlbumAction(Request $request, Album $album)
    {
        $this->denyAccessUnlessGranted('delete', $album, 'You are not allowed to delete this album.');

        $em = $this->getDoctrine()->getManager();
        $em->remove($album);
        $em->flush();

        return new JsonResponse(array('message' => 'Album deleted.'));
    }

    /**
     * @Route("/album/{id}/comment", requirements={
     *     "id": "\d+"
     * })
     * @Method({"POST", "OPTIONS"})
     */
    public function commentAlbumAction(Request $request, Album $album)
    {
        $this->denyAccessUnlessGranted('comment', $album, 'You are not allowed to comment this album.');

        $text = $request->get('text');

        $comment = new AlbumComment();
        $comment->setText($text);
        $comment->setAuthor($this->getUser());
        $comment->setDate(new \DateTime());

        $album->addComment($comment);

        $validator = $this->get('validator');
        $errors = $validator->validate($album);

        if (count($errors) > 0) {
            return new JsonResponse(Util::violationListToJson($errors), 422);
        }

        $em = $this->getDoctrine()->getManager();
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
     * @Method({"DELETE", "OPTIONS"})
     */
    public function deleteAlbumCommentAction(Request $request, $albumId, $commentId)
    {
        $em = $this->getDoctrine()->getManager();
        $album = $em->getRepository('AppBundle:Album')->findOneById($albumId);
        $comment = $em->getRepository('AppBundle:AlbumComment')->findOneById($commentId);

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

        $this->denyAccessUnlessGranted('delete', $comment, 'You are not allowed to delete this comment.');

        $album->removeComment($comment);
        $em->remove($comment);
        $em->flush();

        return new JsonResponse(array(
            'message' => 'Comment deleted.'
        ));
    }

    private function getIsGrantedFilter($property)
    {
        return function($a) use ($property) { return $this->isGranted($property, $a); };
    }

    private function getJsonMapper()
    {
        return function($obj) { return $obj->toJson(); };
    }
}
