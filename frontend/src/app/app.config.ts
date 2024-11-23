import { ApplicationConfig, isDevMode } from '@angular/core';
import { provideRouter } from '@angular/router';

import { routes } from './app.routes';
import { provideClientHydration } from '@angular/platform-browser';
import { provideHttpClient, withFetch } from '@angular/common/http';
import { provideServiceWorker } from '@angular/service-worker';
//import 'babel-polyfill';
//import Pushy from 'pushy-sdk-web';

export const appConfig: ApplicationConfig = {
  //providers: [provideRouter(routes), provideHttpClient(withFetch()), provideServiceWorker('ngsw-worker.js', {
  providers: [provideRouter(routes), provideHttpClient(withFetch()), provideServiceWorker('service-worker.js', {
        //enabled: !isDevMode(),
        enabled:true, 
        registrationStrategy: 'registerWhenStable:30000'
    })]
};
