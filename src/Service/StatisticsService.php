<?php


namespace App\Service;

use App\Repository\CategoryRepository;
use App\Repository\ContactRepository;
use App\Repository\UserRepository;

class StatisticsService
{
    private $categoryRepository;
    private $contactRepository;
    private $userRepository;

    public function __construct(
        CategoryRepository $categoryRepository, 
        ContactRepository $contactRepository,
        UserRepository $userRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->contactRepository = $contactRepository;
        $this->userRepository = $userRepository;
    }

    public function getCategoryCount(): int
    {
        return $this->categoryRepository->countCategories();
    }

    public function getContactMessageCount(): int
    {
        return $this->contactRepository->count([]);
    }

    public function getUserCount(): int
    {
        return $this->userRepository->count([]);
    }

    public function getMessageCount(): int
    {
        return $this->contactRepository->count([]);
    }
}