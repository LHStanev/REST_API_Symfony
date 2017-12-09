<?php

namespace RestApiBundle\Controller;

use RestApiBundle\Entity\Article;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use RestApiBundle\Form\ArticleType;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class ArticleController
 * @package RestApiBundle\Controller
 * @Route("/articles")
 */

class ArticleController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }

    /**
     * @Route("/all",name="all_articles")
     * @Method({"GET"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAllAction() {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findAll();
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($articles, 'json');
        return new Response($json,Response::HTTP_OK,
                     array('content-type' => 'application/json'));
    }

    /**
     * @Route("/{id}", name="article_id", requirements={"id"="\d+"})
     * @param int $id
     * @return Response
     */

    public function showOneAction(int $id)
    {
        $article = $this->getDoctrine()->getRepository(Article::class)->find($id);
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($article, 'json');
        return new Response($json, Response::HTTP_OK, ['content-type' => 'application/json']);

    }

    /**
     * @Route("/create_article",name="create_article")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */

    public function createAction(Request $request) {
        try {
            $this->createArticle($request);
            return new Response(null, Response::HTTP_CREATED);
        }catch (\Exception $e) {
            return new Response(json_encode(['error' => $e->getMessage()]), Response::HTTP_BAD_REQUEST, ['content-type' =>'application/json']);
        }

    }

    protected function createArticle(Request $request) {
        $article = new Article();
        $parameters = $request->request->all();

        $persistedType = $this->processForm($article, $parameters, 'POST');
        return $persistedType;
    }

    private function processForm($article, $params, $method = 'POST') {
       $form = $this->createForm(ArticleType::class, $article, ['method'
        => $method]);
        $form->submit($params);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
            return $article;
        }
        throw new \Exception('submitted data is invalid');
    }
}