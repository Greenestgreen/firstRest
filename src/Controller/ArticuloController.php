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
class ArticuloController extends FOSRestController
{
    

    /**
     * @Rest\Post("/sec/crear_articulo", name="crear_articulo")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Creado con exito"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="No pudimos crear tu articulo :("
     * )
     *
     * @SWG\Parameter(
     *     name="_nombre",
     *     in="body",
     *     type="string",
     *     description="El nombre",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="_contenido",
     *     in="body",
     *     type="string",
     *     description="Contenido del articulo",
     *     schema={}
     * )
     *
     *
     * @SWG\Tag(name="Articulo")
     */
    public function creararticuloAction(Request $request, UserPasswordEncoderInterface $encoder) {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();

        $articulo = [];
        $message = "";

        try {
            $code = 200;
            $error = false;

            $nombre = $request->request->get('_nombre');
            $contenido = $request->request->get('_contenido');

            $articulo = new Articulo();
            $articulo->setNombre($nombre);
            $articulo->setContenido($contenido);
            $articulo->setPropietario($this->getUser());
            $em->persist($articulo);
            $em->flush();

            $message  = "Articulo Creado";

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "Error al crear el articulo :( - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' =>  $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

    /**
     * @Rest\Get("/sec/mis_articulos", name="mis_articulos")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Listado del usuario"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="No se pudo obtener la lista"
     * )
     *
     *
     * @SWG\Tag(name="TraerArticulos")
     */
    public function misarticulosAction(Request $request) {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();

        $articulos = [];
        $message = "";

        try {
            $code = 200;
            $error = false;

            $usuarioId = $this->getUser()->getId();
            

            $articulo = $this->getDoctrine()->getRepository('App:Articulo');
            
            /*$articulos = $em->getRepository("App:Articulo")->findBy([
                "propietario" => $usuarioId,]);*/

            $qb = $articulo->createQueryBuilder('a')
                        ->select("a.id","a.nombre","a.contenido")                       
                        ->andWhere('a.propietario = :id')                        
                        ->setParameter('id', $usuarioId)
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


    /**
     * @Rest\Put("/sec/publicar_articulo/{id}", name="publicar_articulo")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Articulo Publicado"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="No se pudo publicar el articulo"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id del articulo"
     *     
     * )
     *
     *
     * @SWG\Tag(name="PublicarArticulo")
     */
    public function publicararticuloAction(Request $request, $id) {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();

        $articulo = [];
        $message = "";

        try {
            $code = 200;
            $error = false;           

            $articulo = new Articulo();
            $articulo = $this->getDoctrine()->getRepository('App:Articulo')->find($id);

            if(!is_null($articulo) && $this->getUser() == $articulo->getPropietario())
            {
                $articulo->setPublicado(true);
                $em->merge($articulo);
                $em->flush();
                $message  = "Articulo Publicado";
            }
            else
            {
                $message = "No existe el articulo";                
            }


            

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "Error al publicar el articulo :( - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }

     /**
     * @Rest\Delete("/sec/borrar_articulo/{id}", name="borrar_articulo")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Articulo Borrado"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="No se pudo borrar el articulo"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id del articulo"
     *     
     * )
     *
     *
     * @SWG\Tag(name="PublicarArticulo")
     */
    public function borrararticuloAction(Request $request, $id) {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();

        $articulo = [];
        $message = "";

        try {
            $code = 200;
            $error = false;           

            $articulo = new Articulo();
            $articulo = $this->getDoctrine()->getRepository('App:Articulo')->find($id);

            if(!is_null($articulo) && $this->getUser() == $articulo->getPropietario())
            {
                $articulo->setPublicado(true);
                $em->remove($articulo);
                $em->flush();
                $message  = "Articulo Borrado";
            }
            else
            {
                $message = "No existe el articulo";            
            }


            

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "Error al borrar el articulo :( - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }


    /**
     * @Rest\Put("/sec/despublicar_articulo/{id}", name="despublicar_articulo")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Articulo Despublicado"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="No se pudo despublicar el articulo"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id del articulo"
     *     
     * )
     *
     *
     * @SWG\Tag(name="DespublicarArticulo")
     */
    public function despublicararticuloAction(Request $request, $id) {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();

        $articulo = [];
        $message = "";

        try {
            $code = 200;
            $error = false;           

            $articulo = new Articulo();
            $articulo = $this->getDoctrine()->getRepository('App:Articulo')->find($id);

            if(!is_null($articulo) && $this->getUser() == $articulo->getPropietario())
            {
                $articulo->setPublicado(false);
                $em->merge($articulo);
                $em->flush();
                $message  = "Articulo Despublicado";
            }
            else
            {
                $message = "No existe el articulo";               
            }


            

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "Error al despublicar el articulo :( - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }


    /**
     * @Rest\Put("/sec/editar_articulo/{id}", name="editar_articulo")
     *
     * @SWG\Response(
     *     response=201,
     *     description="Articulo Editado"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="No se pudo editar el articulo"
     * )
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id del articulo"
     *     
     * )
      * @SWG\Parameter(
     *     name="contenido",
     *     in="body",
     *     type="string",
     *     description="Contenido del articulo",
     *     schema={}
     * )
     *
     *
     * @SWG\Tag(name="EditarArticulo")
     */
    public function editararticuloAction(Request $request, $id) {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();

        $articulo = [];
        $message = "";

        try {
            $code = 200;
            $error = false;           

            $data = $request->getContent();
            parse_str($data);         
            

            $articulo = new Articulo();
            $articulo = $this->getDoctrine()->getRepository('App:Articulo')->find($id);

            if(!is_null($articulo) && $this->getUser() == $articulo->getPropietario())
            {
                $articulo->setContenido($contenido);
                $em->merge($articulo);
                $em->flush();
                $message  = "Articulo Editado";
            }
            else
            {
                $message = "No existe el articulo";                
            }


            

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "Error al editar el articulo :( - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }


    

}
