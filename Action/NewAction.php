<?php

namespace Perform\ContactBundle\Action;

use Doctrine\ORM\EntityManager;
use Perform\ContactBundle\Entity\Message;
use Perform\BaseBundle\Action\ActionResponse;
use Perform\BaseBundle\Action\ActionInterface;
use Perform\BaseBundle\Crud\CrudRequest;

/**
 * Mark a message as new.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class NewAction implements ActionInterface
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function run(CrudRequest $crudRequest, array $messages, array $options)
    {
        foreach ($messages as $message) {
            $message->setStatus(Message::STATUS_NEW);
            $this->entityManager->persist($message);
        }

        $this->entityManager->flush();

        $response = new ActionResponse(sprintf('%s marked as new.', count($messages) === 1 ? 'Message' : count($messages).' messages'));
        $response->setRedirect(ActionResponse::REDIRECT_LIST_CONTEXT);

        return $response;
    }

    public function getDefaultConfig()
    {
        return [
            'label' => function(CrudRequest $request, Message $message) {
                if ($message->getStatus() === Message::STATUS_SPAM) {
                    return 'Not spam';
                }

                return 'Mark as new';
            },
            'batchLabel' => function(CrudRequest $request) {
                if ($request->getFilter('new') === 'spam') {
                    return 'Not spam';
                }

                return 'Mark as new';
            },
            'isGranted' => function($message) {
                return $message->getStatus() !== Message::STATUS_NEW;
            },
            'isBatchOptionAvailable' => function(CrudRequest $request) {
                return $request->getFilter('new') !== 'new';
            }
        ];
    }
}
