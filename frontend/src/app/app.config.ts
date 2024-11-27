//import { ApplicationConfig, isDevMode } from '@angular/core';
import { ApplicationConfig, importProvidersFrom, isDevMode } from '@angular/core';
import { provideRouter } from '@angular/router';

import { routes } from './app.routes';
//import { provideClientHydration } from '@angular/platform-browser';
//import { provideHttpClient, withFetch } from '@angular/common/http';
//import { provideServiceWorker } from '@angular/service-worker';
//import { ServiceWorkerModule } from '@angular/service-worker';

import { HttpClientModule } from '@angular/common/http';
//import 'babel-polyfill';
//import Pushy from 'pushy-sdk-web';
/*
export const appConfig: ApplicationConfig = {
  //providers: [provideRouter(routes), provideHttpClient(withFetch()), provideServiceWorker('ngsw-worker.js', {
  providers: [provideRouter(routes), provideHttpClient(withFetch()), provideServiceWorker('service-worker.js', {
        //enabled: !isDevMode(),
        enabled:true, 
        registrationStrategy: 'registerWhenStable:30000'
    })]
};*/
/*
  export const appConfig: ApplicationConfig = {
    providers: [
      provideRouter(routes),
      importProvidersFrom(HttpClientModule),
      // Commenté temporairement
      // importProvidersFrom(ServiceWorkerModule.register('ngsw-worker.js', {
      //   enabled: !isDevMode(),
      //   registrationStrategy: 'registerWhenStable:30000'
      // }))
    ]
  };*/
/*
  export const appConfig: ApplicationConfig = {
    providers: [
      provideRouter(routes),
      importProvidersFrom(HttpClientModule),
      importProvidersFrom(ServiceWorkerModule.register('service-worker.js', {
        enabled: true, // Activé même en mode dev pour le test
        registrationStrategy: 'registerWhenStable:30000'
      }))
    ]
  };*/
  export const appConfig: ApplicationConfig = {
    providers: [
      provideRouter(routes),
      importProvidersFrom(HttpClientModule)
    ]
  };
  

