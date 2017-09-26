angular.module('app').run(
    ['$rootScope', '$state', '$stateParams', 'Data',
        function($rootScope, $state, $stateParams, Data) {
            $rootScope.$state = $state;
            $rootScope.$stateParams = $stateParams;
            /** Pengecekan login */
            $rootScope.$on("$stateChangeStart", function(event, toState) {
                Data.get('site/session').then(function(results) {
                    if (results.status_code == 200) {
                        $rootScope.user = results.data.user;
                        /** Check hak akses */
                        // if (globalmenu.indexOf(toState.name) >= 0) {} else {
                        //     if (results.data.user.akses[(toState.name).replace(".", "_")]) {} else {
                        //         $state.go("access.forbidden");
                        //     }
                        // }
                        /** End */
                    } else {
                        $state.go("access.signin");
                    }
                });
            });
            /** End */
        }
    ]).config(
    ['$stateProvider', '$urlRouterProvider',
        function($stateProvider, $urlRouterProvider) {
            $urlRouterProvider.otherwise('/site/dashboard');
            $stateProvider.state('site', {
                    abstract: true,
                    url: '/site',
                    templateUrl: 'tpl/app.html'
                }).state('site.dashboard', {
                    url: '/dashboard',
                    templateUrl: 'tpl/dashboard.html',
                    resolve: {
                        deps: ['$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load('tpl/site/dashboard.js');
                            }
                        ]
                    }
                })
                /** Set default page */
                .state('access', {
                    url: '/access',
                    template: '<div ui-view class="fade-in-right-big smooth"></div>'
                }).state('access.signin', {
                    url: '/signin',
                    templateUrl: 'tpl/page_signin.html',
                    resolve: {
                        deps: ['$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load('tpl/site/site.js').then();
                            }
                        ]
                    }
                }).state('access.404', {
                    url: '/404',
                    templateUrl: 'tpl/page_404.html'
                }).state('access.forbidden', {
                    url: '/forbidden',
                    templateUrl: 'tpl/page_forbidden.html'
                })
                /** End */
                /** Router request master */
                .state('master', {
                    url: '/master',
                    templateUrl: 'tpl/app.html'
                }).state('master.userprofile', {
                    url: '/profile',
                    templateUrl: 'tpl/user/profil.html',
                    resolve: {
                        deps: ['$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load('tpl/user/profil.js');
                            }
                        ]
                    }
                }).state('master.user', {
                    url: '/user',
                    templateUrl: 'tpl/user/user.html',
                    resolve: {
                        deps: ['$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load('tpl/user/user.js');
                            }
                        ]
                    }
                }).state('master.kategoriwisata', {
                    url: '/kategoriwisata',
                    templateUrl: 'tpl/wisata/kategori.html',
                    resolve: {
                        deps: ['$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load('tpl/wisata/kategori.js');
                            }
                        ]
                    }
                }).state('master.wisata', {
                    url: '/wisata',
                    templateUrl: 'tpl/wisata/index.html',
                    resolve: {
                        deps: ['$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load(['angularFileUpload']).then(function() {
                                    return $ocLazyLoad.load('tpl/wisata/index.js');
                                });
                            }
                        ]
                    }
                }).state('master.kategoriberita', {
                    url: '/kategoriberita',
                    templateUrl: 'tpl/berita/kategori.html',
                    resolve: {
                        deps: ['$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load('tpl/berita/kategori.js');
                            }
                        ]
                    }
                }).state('master.berita', {
                    url: '/berita',
                    templateUrl: 'tpl/berita/index.html',
                    resolve: {
                        deps: ['$ocLazyLoad',
                            function($ocLazyLoad) {
                                return $ocLazyLoad.load('tpl/berita/index.js');
                            }
                        ]
                    }
                })
            /** End master request */
        }
    ]);