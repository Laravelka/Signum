import Vue from 'vue';
import moment from 'moment-timezone';

export default {
	install(vue, opts) {
		moment.tz.setDefault("Asia/Yekaterinburg");
		moment.locale('ru');
		
		Object.defineProperty(Vue.prototype, '$moment', { value: moment });
	}
}
