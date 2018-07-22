<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Share\Filter;

use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\Issue;
use App\Service\Interfaces\ImageServiceInterface;
use Symfony\Component\Routing\RouterInterface;

class IssueTransformer extends BatchTransformer
{
    /**
     * @var \App\Api\Transformer\Base\PublicIssueTransformer
     */
    private $issueTransformer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * CraftsmanTransformer constructor.
     *
     * @param \App\Api\Transformer\Base\PublicIssueTransformer $issueTransformer
     * @param RouterInterface $router
     */
    public function __construct(\App\Api\Transformer\Base\PublicIssueTransformer $issueTransformer, RouterInterface $router)
    {
        $this->issueTransformer = $issueTransformer;
        $this->router = $router;
    }

    /**
     * @param Issue $entity
     *
     * @return \App\Api\Entity\Share\Filter\Issue
     */
    public function toApi($entity)
    {
        $issue = new \App\Api\Entity\Share\Filter\Issue($entity->getId());
        $this->issueTransformer->writeApiProperties($entity, $issue);
        if ($entity->getReviewedAt() !== null) {
            $issue->setReviewedAt($entity->getReviewedAt());
            $issue->setReviewedByName($entity->getReviewBy()->getName());
        }

        $routeArguments = ['identifier' => $args['identifier'], 'imageFilename' => $entity->getImageFilename(), 'issue' => $entity->getId()];
        $issue->setImageShareView($this->router->generate('external_image_filter_issue', $routeArguments + ['size' => ImageServiceInterface::SIZE_SHARE_VIEW]));
        $issue->setImageFull($this->router->generate('external_image_filter_issue', $routeArguments + ['size' => ImageServiceInterface::SIZE_FULL]));

        return $issue;
    }
}
