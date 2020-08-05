import Vue from 'vue';
import VueRouter from 'vue-router';

import Home from '@/js/components/Home';
import Login from '@/js/components/Login';
import Notifications from '@/js/components/Notifications';
import Monitor from '@/js/components/Monitor';
import Register from '@/js/components/Register';
import Settings from '@/js/components/Settings';
import NotFound from '@/js/components/NotFound';
import Forbidden from '@/js/components/Forbidden';

import AdminHome from '@/js/components/Admin/Home';
import AdminAdmins from '@/js/components/Admin/Admin';
import AdminUsers from '@/js/components/Admin/Users';
import AdminServers from '@/js/components/Admin/Server';
import AdminSettings from '@/js/components/Admin/Settings';
import AdminNotifications from '@/js/components/Admin/Notifications';

Vue.use(VueRouter);

const router = new VueRouter({
	mode: 'history',
	routes: [
		{
			path: '/',
			name: 'home',
			component: Home,
			meta: {
				auth: true,
				buttonId: 0,
			}
		},
		{
			path: '/login',
			name: 'login',
			component: Login,
			meta: {
				auth: false
			}
		},
		{
			path: '/register',
			name: 'register',
			component: Register,
			meta: {
				auth: {roles: 'user'},
			}
		},
		{
			path: '/monitor/:id',
			name: 'monitor',
			component: Monitor,
			meta: {
				auth: {roles: 'user'},
			}
		},
		{
			path: '/settings',
			name: 'settings',
			component: Settings,
			meta: {
				auth: {roles: 'user'},
				buttonId: 1,
			}
		},
		{
			path: '/notifications',
			name: 'notifications',
			component: Notifications,
			meta: {
				auth: {roles: 'user'},
				buttonId: 2,
			}
		},
		{
			path: '/admin',
			name: 'adminHome',
			component: AdminHome,
			meta: {
				auth: {roles: 'admin'},
			}
		},
		{
			path: '/admin/users',
			name: 'adminUsers',
			component: AdminUsers,
			meta: {
				auth: {roles: 'admin'},
			}
		},
		{
			path: '/admin/servers',
			name: 'adminServers',
			component: AdminServers,
			meta: {
				auth: {roles: 'admin'},
			}
		},
		{
			path: '/admin/admins',
			name: 'adminAdmins',
			component: AdminAdmins,
			meta: {
				auth: {roles: 'admin'},
			}
		},
		{
			path: '/admin/settings',
			name: 'adminSettings',
			component: AdminSettings,
			meta: {
				auth: {roles: 'admin'},
			}
		},
		{
			path: '/admin/notifications',
			name: 'adminNotifications',
			component: AdminNotifications,
			meta: {
				auth: {roles: 'admin'},
			}
		},
		{
			path: '*',
			name: '404',
			component: NotFound
		},
		{
			path: '/403',
			name: '403',
			component: Forbidden
		}
	]
});

export default router;