<?php
namespace App\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\AppBundle\Event\AppEntityEvent;

/**
 * AdminHelperController
 *
 */
class AdminHelperController extends Controller
{

    public function previewAction($entityNamespace, $id, $templatePart = '')
    {

        $renderData = array(
            'entityNamespace' => $entityNamespace,
            'id' => $id,
            'template' => '',
            'templatePart' => $templatePart
        );

        $em = $this->getDoctrine()->getManager();

        $entity = null;

        try {
            $repository = $em->getRepository($entityNamespace);
            $entity = $repository->find($id);
        } catch (\Exception $e) {
            $renderData['error'] = array(
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }

        if ($entity) {

            $reflect = new \ReflectionClass($entity);

            $params = array(
                'entity' => $entity,
                'classShortName' => strtolower($reflect->getShortName())
            );

            try {
                $renderData['template'] = $this->renderView(str_replace(':', ':Admin', $entityNamespace) . ':preview' . $templatePart . '.html.twig', $params);
            } catch (\Exception $exc) {
                $log = $this->container->get('app.log');
                $log->error('AdminHelperController:previewAction', [
                    'code' => $exc->getCode(),
                    'message' => $exc->getMessage()
                ]);
                $renderData['template'] = $this->renderView('AppAppBundle:Helper:preview.html.twig', $params);
            }
        }

        return new JsonResponse($renderData);
    }

    public function sortAction($entityNamespace, Request $request)
    {

        $repository = null;
        $items = [];

        $renderData = array('status' => false);

        $em = $this->getDoctrine()->getManager();

        try {
            $repository = $em->getRepository($entityNamespace);
        } catch (\Exception $e) {
            $renderData['error'] = array(
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }

        $ids = $request->request->get('ids', array());

        $updateItemsCount = 0;

        if ($repository) {
            $items = $repository->findByIds($ids);

            foreach ($items as $item) {
                if (isset($ids[$item->getId()])) {
                    $item->setWeight((int) $ids[$item->getId()]);
                    $em->persist($item);
                    $updateItemsCount++;
                    $renderData['status'] = true;
                }
            }
        }

        $renderData['count'] = $updateItemsCount;

        $em->flush();

        $appEntityEvent = new AppEntityEvent(null, $request, [
            'helper' => 'sortAction',
            'repository' => $repository,
            'items' => $items,
            'renderData' => $renderData
        ]);
        $this->get('event_dispatcher')->dispatch(AppEntityEvent::EVENT_HELPER_SORT, $appEntityEvent);

        return new JsonResponse($renderData);
    }
}
