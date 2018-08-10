<?php
/**
 * ArticuloController.php
 *
 */

namespace App\Controller;


use App\Entity\Articulo;
use App\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

/**
 * Class ApiController
 *
 * @Route("/api")
 */
class ArticulosPublicosController extends FOSRestController
{
    

   /**
     * @Rest\Get("/ver_articulo/{id}", name="ver_articulo")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Articulo encontrado"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="No pudimos obtener el articulo  o no existe :("
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="Id del articulo",
     *     schema={}
     * )
     *
     *
     * @SWG\Tag(name="VerArticulo")
     */
    public function verarticuloAction(Request $request, $id) {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();

        
        $message = "";

        try {

            $code = 200;
            $error = false;

            $articulo = $this->getDoctrine()->getRepository('App:Articulo');

            $qb = $articulo->createQueryBuilder('a')
                        ->select("a.nombre","a.contenido")
                        ->andWhere('a.publicado = true')    
                        ->andWhere('a.id = :id')                        
                        ->setParameter('id', $id)
                        ->getQuery();

            $resultado = $qb->execute();

           

            if(count($resultado) == 0)
            {
                $resultado = "No existe el articulo";
            }
            

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "Error al crear el articulo :( - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $resultado : $message,
        ];


        return new Response($serializer->serialize($response, "json"));


    }


    /**
     * @Rest\Get("/listado_articulos/{cantidad}", name="listar_articulos")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Articulo encontrado"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="No pudimos obtener el articulo  o no existe :("
     * )
     *
     * @SWG\Parameter(
     *     name="cantidad",
     *     in="path",
     *     type="integer",
     *     description="Id del articulo",
     *     schema={}
     * )
     *
     *
     * @SWG\Tag(name="VerListadoArticulos")
     */
     public function listadoarticulosAction(Request $request, $cantidad) {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();

        $articulo = [];
        $message = "";

        try {

            $code = 200;
            $error = false;

            $articulo = $this->getDoctrine()->getRepository('App:Articulo');

            $qb = $articulo->createQueryBuilder('a')
                        ->select("a.id","a.nombre","a.contenido")
                        ->andWhere('a.publicado = true')                        
                        ->orderBy('a.id', 'DESC')
                        ->setMaxResults( $cantidad )
                        ->getQuery();

            $resultado = $qb->execute();
            

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "Error al crear el articulo :( - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $resultado : $message,
        ];


        return new Response($serializer->serialize($response, "json"));


    }



    

}
