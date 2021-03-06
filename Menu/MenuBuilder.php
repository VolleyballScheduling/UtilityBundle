<?php
namespace Volleyball\Bundle\UtilityBundle\Menu;

use \Knp\Menu\FactoryInterface;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\Translation\TranslatorInterface;
use \Symfony\Component\Security\Core\SecurityContextInterface;
use \Symfony\Component\ExpressionLanguage\Expression;

use Volleyball\Bundle\UtilityBundle\Menu\BaseBuilder;

class MenuBuilder extends BaseBuilder
{
    public function __construct(
        FactoryInterface $factory,
        SecurityContextInterface $securityContext,
        TranslatorInterface $translator
    ) {
        parent::__construct($factory, $securityContext, $translator);
    }
    
    /**
     * Navigation menu for a non auth'd user (guest)
     *
     * @param  Symfony\Component\HttpFoundation\Request $request
     * @return MenuItem                                 $menu
     */
    public function createNonAuthMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav pull-right');

        $menu->addChild(
            'home',
            array('route' =>  'homepage')
        );

        $menu->addChild(
            'sign in',
            array('route' =>  'fos_user_security_login')
        );

        $menu->addChild(
            'sign up',
            array('route' =>  'fos_user_registration_register')
        );

        $menu->addChild(
            'about',
            array('route' => 'about')
        );

        $menu->addChild(
            'contact',
            array('route' => 'contact')
        );

        return $menu;
    }

    /**
     * Navigation root menu for auth'd user (attendee, passel_leader,
     * passel_admin, facility_user, faculty, facility_admin, region_user,
     * region_admin, organization_user, organization_admin)
     *
     * @param  Symfony\Component\HttpFoundation\Request $request
     * @return MenuItem                                 $menu
     */
    public function createMainMenu(Request $request)
    {
        if ($this->securityContext->isGranted('ROLE_AUTHENTICATED_ANONYMOUSLY') ||
            !$this->securityContext->isGranted('ROLE_USER')) {
            return $this->createNonauthMenu($request);
        }

        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav pull-right');

        $menu->addChild(
            'home',
            array('route' => 'homepage')
        );

        $menu->addChild($this->activeEnrollmentMenu($request));

        if ($this->securityContext->isGranted('ROLE_ADMIN')) {
            /////////////////////////////////////////////////////////
            ///ADMIN MENU////////////////////////////////////////////
            /////////////////////////////////////////////////////////
            
            /**
             * Organizations
             */
            $menu->addChild('organizations')
                ->setAttribute('dropdown', true);
            $menu['organizations']->addChild(
                'list organizations',
                array('route' => 'volleyball_organization_index')
            );
            $menu['organizations']->addChild(
                'add an organization',
                array('route' => 'volleyball_organization_new')
            );

            /**
             * Passels
             */
            $menu->addChild('passels')
                ->setAttribute('dropdown', true);
            $menu['passels']->addChild(
                'list passels',
                array('route' => 'volleyball_passel_index')
            );
            $menu['passels']->addChild(
                'add a passel',
                array('route' => 'volleyball_passel_new')
            );

            /**
             * Facilities
             */
            $menu->addChild('facilities')
                ->setAttribute('dropdown', true)
                ->setAttribute('icon', 'icon-user');
            $menu['facilities']->addChild(
                'list facilities',
                array('route' => 'volleyball_facility_index')
            );
            $menu['facilities']->addChild(
                'add a facility',
                array('route' => 'volleyball_facility_new')
            );
        } elseif ($this->securityContext->isGranted('ROLE_ORG_USER')) {
            /////////////////////////////////////////////////////////
            ///ORGANIZATION USER MENU////////////////////////////////
            /////////////////////////////////////////////////////////
            
            /**
             * Organization Management
             */
            $menu->addChild('organization management')
                ->setAttribute('dropdown', true);

            $menu['organization management']->addChild(
                'councils',
                array(
                    'route' => 'volleyball_council_by_organization_index',
                    'routeParameters' =>
                        array('slug' => $this->securityContext->getActiveEnrollment()->getOrganization()->getSlug())
                )
            );

            $menu['organization management']->addChild(
                'regions',
                array(
                    'route' => 'volleyball_region_by_organization_index',
                    'routeParameters' =>
                        array('slug' => $this->securityContext->getActiveEnrollment()->getOrganization()->getSlug())
                )
            );

            $menu['organization management']->addChild(
                'facilities',
                array(
                    'route' => 'volleyball_facility_by_organization_index',
                    'routeParameters' =>
                        array('slug' => $this->securityContext->getActiveEnrollment()->getOrganization()->getSlug())
                )
            );

            $menu['organization management']->addChild(
                'passels',
                array(
                    'route' => 'volleyball_passel_by_organization_index',
                    'routeParameters' =>
                        array('slug' => $this->securityContext->getActiveEnrollment()->getOrganization()->getSlug())
                )
            );
        } elseif ($this->securityContext->isGranted('ROLE_COUNCIL_USER')) {
            /////////////////////////////////////////////////////////
            ///COUNCIL USER MENU/////////////////////////////////////
            /////////////////////////////////////////////////////////
            
            /**
             * Council Management
             */
            $menu->addChild('council management')
                ->setAttribute('dropdown', true);

            $menu['council management']->addChild(
                'regions',
                array(
                    'route' => 'volleyball_region_by_council_index',
                    'routeParameters' =>
                        array('alug' => $this->securityContext->getActiveEnrollment()->getCouncil()->getSlug())
                )
            );

            $menu['council management']->addChild(
                'facilities',
                array(
                    'route' => 'volleyball_facility_by_council_index',
                    'routeParameters' =>
                        array('slug' => $this->securityContext->getActiveEnrollment()->getCouncil()->getSlug())
                )
            );
            
            $menu['council management']->addChild(
                'passels',
                array(
                    'route' => 'volleyball_passel_by_council_index',
                    'routeParameters' =>
                        array('slug' => $this->securityContext->getActiveEnrollment()->getCouncil()->getSlug())
                )
            );
        } elseif ($this->securityContext->isGranted('ROLE_REGION_USER')) {
            /////////////////////////////////////////////////////////
            ///REGION USER MENU//////////////////////////////////////
            /////////////////////////////////////////////////////////
            
            /**
             * Region Management
             */
            $menu->addChild('region management')
                ->setAttribute('dropdown', true);

            $menu['region management']->addChild(
                'facilities',
                array(
                    'route' => 'volleyball_facility_by_region_index',
                    'routeParameters' =>
                        array('slug' => $this->securityContext->getActiveEnrollment()->getRegion()->getSlug())
                )
            );
            
            $menu['region management']->addChild(
                'passels',
                array(
                    'route' => 'volleyball_passel_by_region_index',
                    'routeParameters' =>
                        array('slug' => $this->securityContext->getActiveEnrollment()->getRegion()->getSlug())
                )
            );
        } elseif ($this->securityContext->isGranted('ROLE_FACILITY_ADMIN')) {
            /////////////////////////////////////////////////////////
            ////FACILITY ADMIN MENU//////////////////////////////////
            /////////////////////////////////////////////////////////
            
            /**
             * Facility Management
             */
            $menu->addChild('facility management')
                ->setAttribute('dropdown', true);

            $menu['facility management']->addChild(
                'quarters',
                array(
                    'route' => 'volleyball_quarters_by_facility_index',
                    'routeParameters' =>
                        array('slug' => $this->securityContext->getActiveEnrollment()->getFacility()->getSlug())
                )
            );
            
            $menu['facility management']->addChild(
                'faculty',
                array(
                    'route' => 'volleyball_faculty_by_facility_index',
                    'routeParameters' =>
                        array('slug' => $this->securityContext->getActiveEnrollment()->getFacility()->getSlug())
                )
            );
        } elseif ($this->securityContext->isGranted('ROLE_FACILITY_FACULTY')) {
            /////////////////////////////////////////////////////////
            ///FACULTY USER MENU/////////////////////////////////////
            /////////////////////////////////////////////////////////
            
            /**
             * Faculty Management
             */
            $menu->addChild('class management')
                ->setAttribute('dropdown', true);

            $menu['class management']->addChild(
                'record attendence',
                array(
                    'route' => 'volleyball_class_attendence',
                    'routeParameters' => array('slug' => $this->securityContext->getToken()->getUser()->getSlug())
                )
            );
        } elseif ($this->securityContext->isGranted('ROLE_PASSEL_ADMIN')) {
            /////////////////////////////////////////////////////////
            ////PASSEL ADMIN MENU////////////////////////////////////
            /////////////////////////////////////////////////////////
            
            /**
             * Passel Management
             */
            $menu->addChild('passel management')
                ->setAttribute('dropdown', true);

            $menu['passel management']->addChild(
                'factions',
                array(
                    'route' => 'volleyball_faction_by_passel_index',
                    'routeParameters' =>
                        array('slug' => $this->securityContext->getActiveEnrollment()->getPassel()->getSlug())
                )
            );
            
            $menu['passel management']->addChild(
                'attendees',
                array(
                    'route' => 'volleyball_attendee_by_passel_index',
                    'routeParameters' =>
                        array('slug' => $this->securityContext->getActiveEnrollment()->getPassel()->getSlug())
                )
            );
        } elseif ($this->securityContext->isGranted('ROLE_PASSEL_USER')) {
            // attendee custom menu options here.
        }

        $menu->addChild($this->reportMenu($request));
        $menu->addChild($this->profileMenu($request));
        
        /////////////////////////////////////////////////////////
        ////AUTH MENU////////////////////////////////////////////
        /////////////////////////////////////////////////////////
        if ($this->securityContext->isGranted('ROLE_USER')) {
            $menu->addChild(
                'sign out',
                array('route' => 'fos_user_security_logout')
            );
        } else {
            $menu->addChild(
                'sign up',
                array('route' => 'fos_user_register_index')
            );

            $menu->addChild(
                'sign in',
                array('route' => 'fos_user_security_login')
            );
        }

        return $menu;
    }
    
    /**
     * @param Request $request request
     * @return MenuItem 
     */
    public function courseMenu(Request $request)
    {
        $menu = $this->factory->createItem('courses');
        $menu->setAttribute('dropdown', true);
        
        // course index
        $menu->addChild(
            'list courses',
            array(
                'route' => 'volleyball_course_index_by_organization',
                'routeParamters' => array(
                    'organization_slug' => $this->securityContext
                        ->getUser()
                        ->getActiveEnrollment()
                        ->getOrganization()
                        ->getSlug()
                )
            )
        );
        
        // course search
        $menu->addChild(
            'find a course',
            array(
                'route' => 'volleyball__course_search',
                'routeParamters' => array(
                    'organization_slug' => $this->securityContext
                        ->getUser()
                        ->getActiveEnrollment()
                        ->getOrganization()
                        ->getSlug()
                )
            )
        );
        
        // course add
        if ($this->securityContext->isGranted('ROLE_ORGANIZATION_ADMIN')) {
            $menu->addChild(
                'add a course',
                array(
                    'route' => 'volleyball_course_new'
                )
            );
        }
        
        return $menu;
    }
    
    public function facilityMenu(Request $request)
    {
        $menu = $this->factory->createItem('facilities')
            ->setAttribute('dropdown', true);

        // facility index
        $menu->addChild(
            'list facilities',
            array(
                'route' => 'volleyball_facility_index'
            )
        );
        
        // facility search
        $menu->addChild(
            'find a facility',
            array(
                'route' => 'volleyball_facility_search'
            )
        );
        
        // facility add
        if ($this->securityContext->isGranted('ROLE_REGION_ADMIN')) {
            $menu->addChild(
                'add a facility',
                array(
                    'route' => 'volleyball_facility_new'
                )
            );
        }
        
        return $menu;
    }

    /**
     * @param Request $request request
     * @return MenuItem 
     */
    public function reportMenu(Request $request)
    {
        $menu = $this->factory->createItem('reports')
            ->setAttribute('dropdown', true);
        
        $menu->addChild(
            'add a report',
            array('route' => 'homepage')
        );

        return $menu;
    }

    /**
     * @param Request $request request
     * @return MenuItem 
     */
    public function profileMenu(Request $request)
    {
        $menu = $this->factory->createItem('profile')
            ->setAttribute('dropdown', true);

        $menu->addChild(
            'view profile',
            array(
                'route' => 'fos_user_profile_show',
                'routeParameters' => array(
                    'slug' => $this->securityContext->getToken()->getUser()->getSlug()
                )
            )
        );

        $menu->addChild(
            'edit profile',
            array(
                'route' => 'fos_user_profile_edit',
                'routeParameters' => array(
                    'slug' => $this->securityContext->getToken()->getUser()->getSlug()
                )
            )
        );

        $menu->addChild(
            'change password',
            array('route' => 'fos_user_change_password')
        );
        
        return $menu;
    }

    /**
     * @param Request $request request
     * @return MenuItem 
     */
    public function activeEnrollmentMenu(Request $request)
    {
        $enrollment = $this->securityContext->getToken()->getUser()->getActiveEnrollment();

//        $label = array(
//            $this->securityContext->getToken()->getUser()->getUsername().' @ '.
//            $enrollment->getWeek()->getShortName(),
//            $enrollment->getRegion()->getCode().
//            $enrollment->getPassel()->getName().' @ '.
//            $enrollment->getFacility()->getName(). ' - '.
//            $enrollment->getWeek()->GetShortName()
//        );
        $label = array($this->securityContext->getToken()->getUser()->getUsername());

        // Generate menu with corresponding label
        if ($this->securityContext->isGranted(array('ROLE_PASSEL_ADMIN', 'ROLE_PASSEL_LEADER', 'ROLE_PASSEL_USER'))) {
            $menu = $this->factory->createItem($label[0]);
        } else {
            $menu = $this->factory->createItem($label[0]);
        }
        $menu->setAttribute('dropdown', true);

        // quick switchers
        $menu->addChild(
            'change week',
            array('route' => 'volleyball_active_enrollment_week')
        );

        $menu->addChild(
            'change facility',
            array('route' => 'volleyball_active_enrollment_facility')
        );

        if ($this->securityContext->isGranted('ROLE_FACILITY_ADMIN')) {
            $menu->addChild(
                'change passel',
                array('route' => 'volleyball_active_enrollment_passel')
            );
        }

        if ($this->securityContext->isGranted('ROLE_COUNCIL_ADMIN')) {
            $menu->addChild(
                'change region',
                array('route' => 'volleyball_active_enrollment_region')
            );
        }

        if ($this->securityContext->isGranted('ROLE_ORG_ADMIN')) {
            $menu->addChild(
                'change council',
                array('route' => 'volleyball_active_enrollment_council')
            );
        }

        if ($this->securityContext->isGranted('ROLE_ADMIN')) {
            $menu->addChild(
                'change organization',
                array('route' => 'volleyball_active_enrollment_organization')
            );
        }

        // Attendee enrollment
        if ($this->securityContext->isGranted('ROLE_PASSEL_USER')) {
            $menu->addChild(
                'enroll',
                array(
                    'route' => 'volleyball_attendee_enrollment_index',
                    'routeParameters' => array('attendee_slug' => $this->securityContext->getSlug())
                )
            );
        } elseif (!$this->securityContext->isGranted(
            new Expression('"ROLE_FACILITY_FACULTY" or "ROLE_FACILITY_USER" in roles')
        )) {
            $menu->addChild(
                'enroll attendees',
                array(
                    'route' => 'volleyball_attendee_enrollment_index',
                    'routeParameters' => array('passel_slug' => $enrollment->getPassel())
                )
            );
        }

        // Passel enrollment
        if ($this->securityContext->isGranted('ROLE_REGION_ADMIN')) {
            $menu->addChild(
                'enroll passels',
                array(
                    'route' => 'volleyball_passel_enrollment_index',
                    'routeParameters' => array('facility_slug' => $enrollment->getFacility()->getSlug())
                )
            );
        } elseif ($this->securityContext->isGranted('ROLE_PASSEL_ADMIN')) {
            $menu->addChild(
                'enroll passel',
                array(
                    'route' => 'volleyball_passel_enrollment_index',
                    'routeParameters' => array(
                        'facility_slug' => $enrollment->getFacility()->getSlug(),
                        'passel_slug' => $enrollment->getPassel()->getSlug()
                    )
                )
            );
        }
        
        return $menu;
    }
}
