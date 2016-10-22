<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

/**
 * Copy-paste this file to your config/autoload folder (don't forget to remove the .dist extension!)
 */

return [
    'zfc_rbac' => [
        /**
         * Key that is used to fetch the identity provider
         *
         * Please note that when an identity is found, it MUST implements the ZfcRbac\Identity\IdentityProviderInterface
         * interface, otherwise it will throw an exception.
         */
        // 'identity_provider' => 'ZfcRbac\Identity\AuthenticationIdentityProvider',

        /**
         * Set the guest role
         *
         * This role is used by the authorization service when the authentication service returns no identity
         */
         'guest_role' => 'guest',

        /**
         * Set the guards
         *
         * You must comply with the various options of guards. The format must be of the following format:
         *
         *      'guards' => [
         *          'ZfcRbac\Guard\RouteGuard' => [
         *              // options
         *          ]
         *      ]
         */
         'guards' => [
             'ZfcRbac\Guard\ControllerPermissionsGuard'=> [
                // Client 
                [
                    'controller' => 'Client\Controller\Client',
                    'actions'    => ['index', 'list'],
                    'permissions'      => ['client.read']
                ],
                [
                    'controller' => 'Client\Controller\Client',
                    'actions'    => ['add'],
                    'permissions'      => ['client.create']
                ],
                [
                    'controller' => 'Client\Controller\Client',
                    'actions'    => ['delete'],
                    'permissions'      => ['client.delete']
                ],
                 
                // ClientItem
                [
                    'controller' => 'Client\Controller\ClientItem',
                    'actions'    => ['setup', 'index'],
                    'permissions'      => ['client.read']
                ],
                [
                    'controller' => 'Client\Controller\ClientItem',
                    'actions'    => ['newProject'],
                    'permissions'      => ['client.create']
                ],
                [
                    'controller' => 'Client\Controller\ClientItem',
                    'actions'    => ['addressAdd'],
                    'permissions'      => ['contact.create']
                ],
                [
                    'controller' => 'Client\Controller\ClientItem',
                    'actions'    => ['addressFind'],
                    'permissions'      => ['contact.read']
                ],
                [
                    'controller' => 'Client\Controller\ClientItem',
                    'actions'    => ['projects', 'jobs'],
                    'permissions'      => ['project.read']
                ],
                 
                 
                // Building
                [
                    'controller' => 'Client\Controller\Building',
                    'actions'    => ['index'],
                    'permissions'      => ['client.read']
                ],
                [
                    'controller' => 'Client\Controller\Building',
                    'actions'    => ['add'],
                    'permissions'      => ['client.write']
                ],
                [
                    'controller' => 'Client\Controller\Building',
                    'actions'    => ['delete'],
                    'permissions'      => ['client.delete']
                ],
                 
                // Contact
                [
                    'controller' => 'Client\Controller\Contact',
                    'actions'    => ['index', 'item'],
                    'permissions'      => ['contact.read']
                ],
                [
                    'controller' => 'Client\Controller\Contact',
                    'actions'    => ['add'],
                    'permissions'      => ['contact.write']
                ],
                [
                    'controller' => 'Client\Controller\Contact',
                    'actions'    => ['delete'],
                    'permissions'      => ['contact.delete']
                ],
                 
                // JobItem
                [
                    'controller' => 'Job\Controller\JobItem',
                    
                    'actions'    => ['index', 'picklist', 'serials', 'seriallist', 'telemetry', 'setup', 'system', 'model', 'forecast', 'breakdown', 'deliverynote', 'deliverynotelist', 'document', 'viewer','explorer'],
                    'permissions'      => ['project.read']
                ],
                [
                    'controller' => 'Job\Controller\JobItem',
                    'actions'    => ['serialAdd', 'addNote', 'deleteNote'],
                    'permissions'      => ['project.write']
                ], 
                [
                    'controller' => 'Job\Controller\JobItem',
                    'actions'    => ['collaborators'],
                    'permissions'      => ['project.collaborate']
                ], 
                 
                 
                // TrialItem
                [
                    'controller' => 'Trial\Controller\TrialItem',
                    
                    'actions'    => ['index', 'setup', 'telemetry', 'system', 'serials', 'seriallist', 'serialAdd', 'deliverynote', 'deliverynotelist', 'document', 'viewer','explorer'],
                    'permissions'      => ['project.read']
                ],
                [
                    'controller' => 'Trial\Controller\TrialItem',
                    'actions'    => ['close', 'start', 'completed', 'addNote', 'deleteNote'],
                    'permissions'      => ['project.write']
                ], 
                [
                    'controller' => 'Trial\Controller\TrialItem',
                    'actions'    => ['collaborators'],
                    'permissions'      => ['project.collaborate']
                ], 
                 
                 
                // ProjectItem
                [
                    'controller' => 'Project\Controller\ProjectItem',
                    'actions'    => ['setup', 'index', 'telemetry', 'model', 'forecast', 'breakdown', 'spaceList', 'configRefresh', 'filemanager', 'fileManagerRetrieve'],
                    'permissions'      => ['project.read']
                ],
                [
                    'controller' => 'Project\Controller\ProjectItem',
                    'actions'    => ['system', 'newSpace', 'configLoad', 'configSave', 'fileManagerUpload', 'addNote'],
                    'permissions'      => ['project.write']
                ], 
                [
                    'controller' => 'Project\Controller\ProjectItem',
                    'actions'    => ['collaborators'],
                    'permissions'      => ['project.collaborate']
                ], 
                 
                 
                 
                // ProjectItemDocument
                [
                    'controller' => 'Project\Controller\ProjectItemDocumentController',
                    'actions'    => ['explorer'],
                    'permissions'      => ['project.explorer.read']
                ],
                 
                [
                    'controller' => 'Project\Controller\ProjectItemDocumentController',
                    'permissions'      => ['project.write']
                ],
                 
                // SpaceItemDocument
                [
                    'controller' => 'Space\Controller\SpaceItem',
                    'actions'    => ['index', 'retrieveSystem', 'architecturalCalculate', 'photoList', 'photoRetrieve'],
                    'permissions'      => ['project.read']
                ],
                [
                    'controller' => 'Space\Controller\SpaceItem',
                    'actions'    => ['add', 'update', 'addNote', 'deleteNote', 'deleteSystem', 'photoUpload'],
                    'permissions'      => ['project.write']
                ], 
                 
                // Product
                [
                    'controller' => 'Product\Controller\Product',
                    'actions'    => ['catalog', 'list'],
                    'permissions'      => ['product.read']
                ],
                [
                    'controller' => 'Product\Controller\Product',
                    'actions'    => ['add', ],
                    'permissions'      => ['product.create']
                ], 
                 
                // ProductItem
                [
                    'controller' => 'Product\Controller\ProductItem',
                    'actions'    => ['index'],
                    'permissions'      => ['product.read']
                ],
                [
                    'controller' => 'Product\Controller\ProductItem',
                    'actions'    => ['edit', ],
                    'permissions'      => ['product.write']
                ], 
                 
               // Legacy
                [
                    'controller' => 'Product\Controller\Legacy',
                    'actions'    => ['catalog', 'list'],
                    'permissions'      => ['product.read']
                ],
                [
                    'controller' => 'Product\Controller\Legacy',
                    'actions'    => ['add', ],
                    'permissions'      => ['product.create']
                ], 
                 
                // LegacyItem
                [
                    'controller' => 'Product\Controller\LegacyItem',
                    'actions'    => ['index'],
                    'permissions'      => ['product.read']
                ],
                [
                    'controller' => 'Product\Controller\LegacyItem',
                    'actions'    => ['edit', ],
                    'permissions'      => ['product.write']
                ], 
                 
                 
                // Task
                [
                    'controller' => 'Task\Controller\Task',
                    'actions'    => ['index'],
                    'permissions'      => ['task.read']
                ],
                [
                    'controller' => 'Task\Controller\Task',
                    'actions'    => ['add', ],
                    'permissions'      => ['task.create']
                ], 
                 
                // TaskItem
                [
                    'controller' => 'Task\Controller\TaskItem',
                    'actions'    => ['index'],
                    'permissions'      => ['task.read']
                ],
                [
                    'controller' => 'Task\Controller\TaskItem',
                    'actions'    => ['edit', ],
                    'permissions'      => ['task.write']
                ], 
                [
                    'controller' => 'Task\Controller\TaskItem',
                    'actions'    => ['delete', ],
                    'permissions'      => ['task.delete']
                ], 

                // Playground 
                [
                    'controller' => 'Application\Controller\Playground',
                    //'actions'    => ['index', 'routemapping'],
                    'permissions'      => ['admin.playground']
                ],
                 
             ]
         ],

        /**
         * As soon as one rule for either route or controller is specified, a guard will be automatically
         * created and will start to hook into the MVC loop.
         *
         * If the protection policy is set to DENY, then any route/controller will be denied by
         * default UNLESS it is explicitly added as a rule. On the other hand, if it is set to ALLOW, then
         * not specified route/controller will be implicitly approved.
         *
         * DENY is the most secure way, but it is more work for the developer
         */
        // 'protection_policy' => \ZfcRbac\Guard\GuardInterface::POLICY_ALLOW,

        /**
         * Configuration for role provider
         *
         * It must be an array that contains configuration for the role provider. The provider config
         * must follow the following format:
         *
         *      'ZfcRbac\Role\InMemoryRoleProvider' => [
         *          'role1' => [
         *              'children'    => ['children1', 'children2'], // OPTIONAL
         *              'permissions' => ['edit', 'read'] // OPTIONAL
         *          ]
         *      ]
         *
         * Supported options depend of the role provider, so please refer to the official documentation
         */
        'role_provider' => [
            'ZfcRbac\Role\ObjectRepositoryRoleProvider' => [
                'object_manager'     => 'doctrine.entitymanager.orm_default', // alias for doctrine ObjectManager
                'class_name'         => 'User\Entity\HierarchicalRole', // FQCN for your role entity class
                'role_name_property' => 'name', // Name to show
            ],
        ],

        /**
         * Configure the unauthorized strategy. It is used to render a template whenever a user is unauthorized
         */
        'unauthorized_strategy' => [
            /**
             * Set the template name to render
             */
            'template' => 'error/403'
        ],

        /**
         * Configure the redirect strategy. It is used to redirect the user to another route when a user is
         * unauthorized
         */
        'redirect_strategy' => [
            /**
             * Enable redirection when the user is connected
             */
            //'redirect_when_connected' => true,

            /**
             * Set the route to redirect when user is connected (of course, it must exist!)
             */
            // 'redirect_to_route_connected' => 'login',//'home',

            /**
             * Set the route to redirect when user is disconnected (of course, it must exist!)
             */
            //'redirect_to_route_disconnected' => 'login',

            /**
             * If a user is unauthorized and redirected to another route (login, for instance), should we
             * append the previous URI (the one that was unauthorized) in the query params?
             */
            // 'append_previous_uri' => true,

            /**
             * If append_previous_uri option is set to true, this option set the query key to use when
             * the previous uri is appended
             */
            // 'previous_uri_query_key' => 'redirectTo'
        ],

        /**
         * Various plugin managers for guards and role providers. Each of them must follow a common
         * plugin manager config format, and can be used to create your custom objects
         */
        // 'guard_manager'               => [],
        // 'role_provider_manager'       => []
    ]
];