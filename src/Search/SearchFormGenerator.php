<?php

namespace App\Search;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;

class SearchFormGenerator
{
    private FormFactoryInterface $formFactory;

    /**
     * @param FormFactory $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function getSearchForm(): FormView{

        $form = $this->formFactory->create(SearchType::class);
        return $form->createView();
    }
}