<template>
	<v-app id="inspire">
		<v-navigation-drawer
			app
			v-model="drawer"
			v-if="$auth.ready() && $auth.check()"
		>
			<v-list-item>
				<v-list-item-content>
					<v-list-item-title class="title">
						Signum.video
					</v-list-item-title>
					<v-list-item-subtitle>
						Видеонаблюдение в вашем доме
					</v-list-item-subtitle>
				</v-list-item-content>
			</v-list-item>
			<v-divider></v-divider>
			<v-list dense>
				<v-list-item-group v-model="activeBtn">
					<v-list-item v-for="item in drawerLinks[$auth.user().roles]" v-bind:key="item.icon" @click.prevent="clickLink(item)" link>
						<v-list-item-action>
							<v-icon>{{ item.icon }}</v-icon>
						</v-list-item-action>
						<v-list-item-content>
							<v-list-item-title>{{ item.title }}</v-list-item-title>
						</v-list-item-content>
					</v-list-item>
				</v-list-item-group>
			</v-list>
		</v-navigation-drawer>
		<div v-if="$router.currentRoute.path !== '/' && $router.currentRoute.path !== '/admin'">
			<v-app-bar v-if="$auth.check() && !hide.appBar" app dark>
				<v-btn icon @click="goBack">
					<v-icon>mdi-arrow-left</v-icon>
				</v-btn>
				<v-toolbar-title>{{ title }}</v-toolbar-title>
				<v-spacer></v-spacer>
				<v-btn icon @click="onRefresh">
					<v-icon>mdi-refresh</v-icon>
				</v-btn>
			</v-app-bar>
		</div>
		<div v-else>
			<v-app-bar v-if="$auth.check() && !hide.appBar" app dark>
				<v-app-bar-nav-icon @click.stop="drawer = !drawer" />
				<v-toolbar-title>{{ title }}</v-toolbar-title>
				<v-spacer></v-spacer>
				<v-btn icon @click="onRefresh">
					<v-icon>mdi-refresh</v-icon>
				</v-btn>
			</v-app-bar>
		</div>
		<v-content>
			<router-view></router-view>
		</v-content>
		<v-bottom-navigation v-if="$auth.check() && !hide.bottomNavigation && $auth.user().roles != 'admin'"  :value="activeBtn" grow hide-on-scroll fixed app>
			<v-btn :to="{name: 'home'}">
				<v-icon>mdi-home</v-icon>
			</v-btn>
			<v-btn :to="{name: 'home'}">
				<v-icon>mdi-bell</v-icon>
			</v-btn>
			<v-btn :to="{name: 'settings'}">
				<v-icon>mdi-settings</v-icon>
			</v-btn>
		</v-bottom-navigation>
	</v-app>
</template>

<script>
	export default {
		props: {
			source: String,
		},
		data: () => ({
			hide: {
				appBar: false,
				bottomNavigation: false
			},
			title: 'Signum.video',
			drawer: null,
			activeBtn: null,
			drawerLinks: {
				user: [
					{to: {name: 'home'}, title: 'Главная', icon: 'mdi-home'},
					{to: {name: 'home'}, title: 'Уведомления', icon: 'mdi-bell'},
					{to: {name: 'settings'}, title: 'Настройки', icon: 'mdi-settings'},
					{title: 'Выход', icon: 'mdi-exit-to-app', isExit: true}
				],
				admin: [
					{to: {name: 'adminHome'}, title: 'Главная', icon: 'mdi-home'},
					{to: {name: 'adminNotifications'}, title: 'Уведомления', icon: 'mdi-bell'},
					{to: {name: 'adminSettings'}, title: 'Настройки', icon: 'mdi-settings'},
					{title: 'Выход', icon: 'mdi-exit-to-app', isExit: true}
				]
			}
		}),
		watch: {
			'$route': function(newRoute) {
				if (newRoute.meta.buttonId)
				{
					this.activeBtn = newRoute.meta.buttonId;
				}
			}
		},
		methods: {
			clickLink(data) {
				if (data.isExit)
				{
					this.$auth.logout();
				}
				else
				{
					if (this.$router.currentRoute.name != data.to.name)
						this.$router.push(data.to);
				}
			},
			goBack() {
				window.history.length > 1 ? this.$router.go(-1) : this.$router.push('/')
			},
			onRefresh() {
				this.$root.$emit('clickedRefreshButton', true);
			}
		},
		mounted() {
			var app = this;
			
			app.$root.$on('hide-object', function(data) {
				data.forEach((item) => {
					app.hide[item] = true;
				});
			});
			
			app.$root.$on('active-panel', function(data) {
				app.title = data.title;
				app.activeBtn = data.id; 
				document.title = data.title;
			});
		}
	}
</script>
