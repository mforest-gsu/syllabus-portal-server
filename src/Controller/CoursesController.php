<?php

declare(strict_types=1);

namespace Gsu\SyllabusPortal\Controller;

use Gsu\SyllabusPortal\Repository\CourseSectionQueryParams;
use Gsu\SyllabusPortal\Repository\CourseSectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CoursesController extends AbstractController
{
    #[Route(
        name: 'courses.list',
        methods: 'GET',
        path: '/courses',
        format: 'json'
    )]
    public function list(
        CourseSectionRepository $courseSectionRepo,
        SerializerInterface $serializer,
        #[MapQueryString] CourseSectionQueryParams $params = new CourseSectionQueryParams()
    ): Response {
        [$data, $count] = $courseSectionRepo->fetch($params);

        return new JsonResponse(
            $serializer->serialize([
                'result' => [
                    'count' => $count,
                    'data' => $data
                ]
            ], 'json'),
            200,
            [],
            true
        );
    }
}
