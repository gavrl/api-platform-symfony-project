<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use ApiPlatform\Core\Validator\ValidatorInterface;
use ApiPlatform\Core\Validator\Exception\ValidationException;

class UploadImageAction
{
    /**
     * @var FormFactoryInterface
     */
    private FormFactoryInterface $formFactory;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function __invoke(Request $request)
    {
        // create a new image instance
        $image = new Image();

        // validate the form
        $form = $this->formFactory->create(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // persist the new image entity
            $this->entityManager->persist($image);
            $this->entityManager->flush();

            $image->setFile(null);

            return $image;
        }

        throw new ValidationException(
            $this->validator->validate($image)
        );
    }
}