<?

namespace Cms\Controller\Auth;

use Appcia\Webwork\Controller;
use App\Entity\Auth\Group;
use App\Entity\Auth\GroupRepository;
use Cms\Form\Auth\GroupType;

class GroupController extends Controller
{
    /**
     * @return GroupRepository
     */
    private function getRepository()
    {
        return $this->get('em')
            ->getRepository('App\Entity\Auth\Group');
    }

    /**
     * @return Group
     */
    private function getGroup()
    {
        $id = $this->getRequest()
            ->get('groupId');

        $group = $this->getRepository()
            ->find($id);

        if ($group === null) {
            $this->goNotFound();
        }

        return $group;
    }

    /**
     * @return array
     */
    public function listAction()
    {
        return array(
            'groups' => $this->getRepository()->findAll()
        );
    }

    /**
     * @return array
     */
    public function addAction()
    {
        $form = new GroupType($this->get('context'), $this->get('rm'));

        $admin = $this->get('auth')->getUser();
        $token = $form->tokenize($admin->getId());

        if ($this->getRequest()->isPost()) {
            $form->populate($this->getRequest()->getData())
                ->skip($this->getRequest()->get('skip'))
                ->unskip($this->getRequest()->get('unskip'))
                ->load($token);

            if (!$form->skipChanged()) {
                if ($form->process()) {
                    $group = new Group();
                    $group->setName($form->get('name'))
                        ->setDescription($form->get('description'))
                        ->setIcon($form->get('icon'));

                    try {
                        $em = $this->get('em');
                        $em->persist($group);
                        $em->flush();

                        $this->get('flash')
                            ->success(sprintf("Group '%s' added successfully.", $group->getName()));

                        $this->goRoute('cms-group-list');
                    } catch (\Exception $e) {
                        $this->get('flash')
                            ->error('Cannot create a group.');
                    }
                } else {
                    $this->get('flash')
                        ->warning("Invalid data. Please correct errors and try again.");
                }
            }
        } else {
            $form->unload($token);
        }

        return array(
            'form' => $form
        );
    }

    /**
     * @return array
     */
    public function editAction()
    {
        $form = new GroupType($this->get('context'), $this->get('rm'));

        $admin = $this->get('auth')->getUser();
        $token = $form->tokenize($admin->getId());

        $group = $this->getGroup();

        if ($this->getRequest()->isPost()) {
            $form->populate($this->getRequest()->getData())
                ->skip($this->getRequest()->get('skip'))
                ->unskip($this->getRequest()->get('unskip'))
                ->load($token, array('icon' => $group->getIcon()));

            if (!$form->skipChanged()) {
                if ($form->process()) {
                    $group->setName($form->get('name'))
                        ->setDescription($form->get('description'))
                        ->setIcon($form->get('icon'));

                    try {
                        $em = $this->get('em');
                        $em->persist($group);
                        $em->flush();

                        $group->saveResources($em->createLifecycleEventArgs($group));

                        $this->get('flash')
                            ->success(sprintf("Group '%s' edited successfully.", $group->getName()));

                        $this->goRoute('cms-group-list');
                    } catch (\Exception $e) {
                        $this->get('flash')
                            ->error('Cannot edit group.');
                    }
                } else {
                    $this->get('flash')
                        ->warning("Invalid data. Please correct errors and try again.");
                }
            }
        } else {
            $form->unload($token);
            $form->suck($group);
        }

        return array(
            'group' => $group,
            'form' => $form,
        );
    }

    /**
     * @return void
     */
    public function removeAction()
    {
        $group = $this->getGroup();

        try {
            $em = $this->get('em');
            $em->remove($group);
            $em->flush();

            $this->get('flash')
                ->success(sprintf("Group '%s' removed successfully.", $group->getName()));
        } catch (\Exception $e) {
            $this->get('flash')
                ->error(sprintf("Cannot remove group '%s'", $group->getName()));
        }

        $this->goRoute('cms-group-list');
    }
}