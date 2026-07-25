<?php

namespace Mosparo\Twig;

use Mosparo\Entity\ProjectGroup;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProjectGroupExtension extends AbstractExtension
{
    protected RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('find_project_group_form_field', [$this, 'findProjectGroupFormField']),
            new TwigFunction('get_project_group_breadcrumbs', [$this, 'getProjectGroupBreadcrumbs']),
        ];
    }

    public function findProjectGroupFormField(FormView $form, ?ProjectGroup $group): ?FormView
    {
        foreach ($form->getIterator() as $child) {
            if (!isset($child->vars['attr']['data-project-group-id'])) {
                if (!$group) {
                    return $child;
                }
                continue;
            }

            if ($child->vars['attr']['data-project-group-id'] === $group->getId()) {
                return $child;
            }
        }

        return null;
    }

    public function getProjectGroupBreadcrumbs(ProjectGroup $projectGroup): array
    {
        $breadcrumbItems = [
            [
                'title' => $projectGroup->getName(),
                'url' => $this->router->generate('project_list_group', ['projectGroup' => $projectGroup->getId()]),
            ]
        ];

        $parent = $projectGroup->getParent();
        while ($parent != null) {
            $breadcrumbItems[] = [
                'title' => $parent->getName(),
                'url' => $this->router->generate('project_list_group', ['projectGroup' => $parent->getId()]),
            ];
            $parent = $parent->getParent();
        }

        return $breadcrumbItems;
    }
}
