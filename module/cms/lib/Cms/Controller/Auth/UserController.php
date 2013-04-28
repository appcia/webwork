<?

namespace Cms\Controller\Auth;

use Appcia\Webwork\Controller;
use App\Entity\Auth\User;
use App\Entity\Auth\UserRepository;
use App\Entity\Auth\GroupRepository;
use Cms\Form\Auth\UserEdit;
use Cms\Form\Auth\UserAdd;
use Cms\Form\Auth\UserLogin;
use Cms\Form\Auth\UserChangePassword;

class UserController extends Controller
{
    /**
     * @return UserRepository
     */
    private function getUserRepository()
    {
        return $this->get('em')
            ->getRepository('App\Entity\Auth\User');
    }

    /**
     * @return GroupRepository
     */
    private function getGroupRepository()
    {
        return $this->get('em')
            ->getRepository('App\Entity\Auth\Group');
    }

    /**
     * @return User
     */
    private function getUser()
    {
        $id = $this->getRequest()
            ->get('userId');

        $user = $this->getUserRepository()
            ->find($id);

        if ($user === null) {
            $this->goNotFound();
        }

        return $user;
    }

    /**
     * @return array
     */
    public function listAction()
    {
        $users = $this->getUserRepository()
            ->findAll();

        return array(
            'users' => $users
        );
    }

    /**
     * @return array
     */
    public function addAction()
    {
        $form = new UserAdd($this->get('context'), $this->get('rm'));

        $admin = $this->get('auth')->getUser();
        $token = $form->tokenize($admin->getId());

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getData();
            $form->populate($data)
                ->skip($this->getRequest()->get('skip'))
                ->unskip($this->getRequest()->get('unskip'))
                ->load($token);

            if (!$form->skipChanged()) {
                if ($form->process()) {
                    $email = $form->get('email');
                    $user = $this->getUserRepository()
                        ->findOneBy(array('email' => $email));

                    if ($user !== null) {
                        $this->get('flash')
                            ->info(sprintf("User with e-mail '%s' already exists", $email));
                    } else {
                        $user = new User();
                        $user->setEmail($form->get('email'))
                            ->setPassword($form->get('password', true))
                            ->setName($form->get('name'))
                            ->setSurname($form->get('surname'))
                            ->setNick($form->get('nick'))
                            ->setBirth($form->get('birth'))
                            ->setAvatar($form->get('avatar'));

                        $groupIds = !empty($data['group']) ? $data['group'] : array();
                        $groups = $this->getGroupRepository()
                            ->findById($groupIds);

                        foreach ($groups as $group) {
                            $user->getGroups()
                                ->add($group);
                        }

                        try {
                            $em = $this->get('em');
                            $em->persist($user);
                            $em->flush();

                            $this->get('flash')
                                ->success(sprintf("User '%s' added successfully.", $user->getFullname()));

                            $this->goRoute('cms-user-list');
                        } catch (\Exception $e) {
                            $this->get('flash')
                                ->error('Cannot create a user.');
                        }
                    }
                } else {
                    $this->get('flash')
                        ->warning("Invalid data. Please correct errors and try again.");
                }
            }

        } else {
            $form->unload($token);
        }

        $groups = $this->getGroupRepository()
            ->findAllOrderByName();

        return array(
            'groups' => $groups,
            'form' => $form
        );
    }

    /**
     * @return array
     */
    public function editAction()
    {
        $form = new UserEdit($this->get('context'), $this->get('rm'));

        $user = $this->getUser();
        $admin = $this->get('auth')->getUser();
        $token = $form->tokenize($admin->getId());

        if ($this->getRequest()->isPost()) {
            $form->populate($this->getRequest()->getData())
                ->skip($this->getRequest()->get('skip'))
                ->unskip($this->getRequest()->get('unskip'))
                ->load($token, array('avatar' => $user->getAvatar()));

            if (!$form->skipChanged()) {
                if ($form->process()) {
                    $email = $form->get('email');
                    $otherUser = $this->getUserRepository()
                        ->findOneBy(array('email' => $email));

                    if ($email !== $user->getEmail() && $otherUser !== null) {
                        $this->get('flash')
                            ->info(sprintf("User with e-mail '%s' already exists.", $otherUser->getEmail()));
                    } else {
                        $user->setEmail($form->get('email'))
                            ->setName($form->get('name'))
                            ->setSurname($form->get('surname'))
                            ->setNick($form->get('nick'))
                            ->setBirth($form->get('birth'))
                            ->setAvatar($form->get('avatar'));

                        $groupIds = $form->get('group');
                        if (!empty($groupIds)) {
                            $groups = $this->getGroupRepository()
                                ->findById($groupIds);

                            $user->getGroups()->clear();
                            foreach ($groups as $group) {
                                $user->getGroups()
                                    ->add($group);
                            }
                        }

                        try {
                            $em = $this->get('em');
                            $em->persist($user);
                            $em->flush();

                            $this->get('flash')
                                ->success(sprintf("User '%s' edited successfully.", $user->getFullname()));

                            $this->goRoute('cms-user-list');
                        } catch (\Exception $e) {
                            $this->get('flash')
                                ->error('Cannot edit user.');
                        }
                    }

                } else {
                    $this->get('flash')
                        ->warning("Invalid data. Please correct errors and try again.");
                }
            }
        } else {
            $form->unload($token);
            $form->suck($user);
            $form->set('group', $user->getGroupIds());
        }

        $groups = $this->getGroupRepository()
            ->findAllOrderByName();

        return array(
            'user' => $user,
            'groups' => $groups,
            'form' => $form
        );
    }

    /**
     * @return void
     */
    public function removeAction()
    {
        $user = $this->getUser();

        try {
            $em = $this->get('em');
            $em->remove($user);
            $em->flush();

            $this->get('flash')
                ->success(sprintf("User '%s' removed successfully.", $user->getFullname()));
        } catch (\Exception $e) {
            $this->get('flash')
                ->error(sprintf("Cannot remove user '%s'", $user->getFullname()));
        }

        $this->goRoute('cms-user-list');
    }

    /**
     * @return array
     */
    public function loginAction()
    {
        $form = new UserLogin($this->get('context'));

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getData();
            $form->populate($data);

            if ($form->process()) {
                $email = $form->get('email');
                $user = $this->getUserRepository()
                    ->findOneBy(array('email' => $email));

                if ($user === null) {
                    $this->get('flash')
                        ->info(sprintf("User with e-mail '%s' does not exist.", $email));
                } else {
                    $password = $user->cryptPassword($form->get('password'));
                    if ($user->getPassword() != $password) {
                        $this->get('flash')
                            ->warning('Invalid password.');
                    } else {
                        $this->get('flash')
                            ->success('Logged successfully.');

                        $this->get('auth')
                            ->authorize($user);

                        $this->goRoute('cms-page-home');
                    }
                }
            } else {
                $this->get('flash')
                    ->warning("Invalid data. Please correct errors and try again.");
            }
        }

        return array(
            'form' => $form
        );
    }

    /**
     * @return void
     */
    public function logoutAction()
    {
        $this->get('auth')->unauthorize();

        $this->goRoute('cms-user-login');
    }

    /**
     * @return array
     */
    public function changePasswordAction()
    {
        $form = new UserChangePassword($this->get('context'));

        $user = $this->getUser();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getData();
            $form->populate($data);

            if ($form->process()) {
                $passwordActual = $user->cryptPassword($form->get('passwordActual'));
                $passwordNew = $user->cryptPassword($form->get('password'));


                if ($user->getPassword() != $passwordActual) {
                    $this->get('flash')
                        ->warning('Actual password does not match.');
                } elseif ($user->getPassword() == $passwordNew) {
                    $this->get('flash')
                        ->info('New password does not differ to actual.');
                } else {
                    $user->setPassword($passwordNew);

                    try {
                        $em = $this->get('em');
                        $em->persist($user);
                        $em->flush();

                        $this->get('flash')
                            ->success("Password changed successfully.");

                        $this->goRoute('cms-user-list');
                    } catch (Exception $e) {
                        $this->get('flash')
                            ->error('Cannot change password.');
                    }
                }
            } else {
                $this->get('flash')
                    ->warning("Invalid data. Please correct errors and try again.");
            }
        }

        return array(
            'user' => $user,
            'form' => $form
        );
    }
}