<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Controller;

use Gsu\SyllabusPortal\Entity\User;
use Gsu\SyllabusPortal\Repository\CourseSectionRepository;
use Gsu\SyllabusPortal\Repository\CvRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;

class CvController extends AbstractController
{
    public function __construct(
        private CourseSectionRepository $courseSectionRepo,
        private CvRepository $cvRepo,
        private SerializerInterface $serializer
    ) {
        // empty
    }


    #[Route(
        name: 'cv.add',
        methods: 'POST',
        path: '/cv/{id}',
        format: 'json'
    )]
    public function add(
        Request $request,
        string $id
    ): JsonResponse {
        $courseSection = $this->courseSectionRepo->find($id) ?? throw new NotFoundHttpException();
        if (!$courseSection->hasInstructor()) {
            throw new NotFoundHttpException();
        }

        $this->cvRepo->addCv(
            $courseSection,
            $this->getAppUser(),
            $this->getCvFile($request)->getPathname()
        );

        return $this->createResponse($id);
    }


    #[Route(
        name: 'cv.remove',
        methods: 'DELETE',
        path: '/cv/{id}',
        format: 'json'
    )]
    public function remove(string $id): JsonResponse
    {
        $courseSection = $this->courseSectionRepo->find($id) ?? throw new NotFoundHttpException();
        if (!$courseSection->hasCv()) {
            throw new NotFoundHttpException();
        }

        $this->cvRepo->removeCv($courseSection);

        return $this->createResponse($id);
    }


    private function getAppUser(): User
    {
        /** @var User $user */
        $user = $this->getUser() ?? throw new AccessDeniedException();
        return $user;
    }


    /**
     * @param Request $request
     * @return UploadedFile
     */
    private function getCvFile(Request $request): UploadedFile
    {
        $syllabusFile = null;
        foreach ($request->files->all() as $file) {
            if ($file instanceof UploadedFile) {
                $syllabusFile = $file;
                break;
            }
        }

        if ($syllabusFile === null) {
            throw new BadRequestException();
        }

        switch (mime_content_type($syllabusFile->getPathname())) {
            case 'application/pdf':
                break;
            default:
                throw new BadRequestException();
        }

        $fileSize = filesize($syllabusFile->getPathname());
        if ($fileSize < 1024 || $fileSize > 5000000) {
            throw new BadRequestException();
        }

        return $syllabusFile;
    }


    private function createResponse(string $id): JsonResponse
    {
        $response = [
            'result' => $this->courseSectionRepo->findWithUrl($id) ?? throw new \RuntimeException()
        ];

        return new JsonResponse(
            $this->serializer->serialize($response, 'json'),
            200,
            [],
            true
        );
    }
}
