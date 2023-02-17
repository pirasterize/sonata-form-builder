<?php

namespace Pirastru\FormBuilderBundle\Controller;

use Pirastru\FormBuilderBundle\Entity\FormBuilder;
use Pirastru\FormBuilderBundle\Entity\FormBuilderSubmission;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Exception\ModelManagerThrowable;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

class FormBuilderAdminController extends CRUDController
{
    #[Entity("submission", expr: "repository.find(submission_id)")]
    public function submissionDeleteAction(Request $request, FormBuilder $formBuilder, FormBuilderSubmission $submission): Response
    {
        if (Request::METHOD_GET === $request->getMethod()) {
            return $this->renderWithExtraParams('@PirastruFormBuilder/CRUD/submission_delete.html.twig', [
                'form' => $formBuilder,
                'submission' => $submission,
                'action' => 'delete',
                'csrf_token' => $this->getCsrfToken('sonata.delete')
            ]);
        }

        $this->admin->checkAccess('delete', $formBuilder);

        if (\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_DELETE], true)) {
            $objectName = $this->admin->toString($submission);

            $this->validateCsrfToken($request, 'sonata.delete');

            try {
                $this->admin->delete($submission);

                $this->addFlash(
                    'sonata_flash_success',
                    $this->trans(
                        'flash_delete_success',
                        ['%name%' => $this->escapeHtml($objectName)],
                        'SonataAdminBundle'
                    )
                );
            } catch (ModelManagerException $e) {
                // NEXT_MAJOR: Remove this catch.
                $this->handleModelManagerException($e);

                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        'flash_delete_error',
                        ['%name%' => $this->escapeHtml($objectName)],
                        'SonataAdminBundle'
                    )
                );
            } catch (ModelManagerThrowable $e) {
                $errorMessage = $this->handleModelManagerThrowable($e);

                $this->addFlash(
                    'sonata_flash_error',
                    $errorMessage ?? $this->trans(
                    'flash_delete_error',
                    ['%name%' => $this->escapeHtml($objectName)],
                    'SonataAdminBundle'
                )
                );
            }

            return new RedirectResponse($this->admin->generateUrl('show', ["id" => $formBuilder->getId()]));
        }

    }
}
