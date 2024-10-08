/*
import { Injectable, PLATFORM_ID, Inject } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';
// import * as L from 'leaflet';

let L: any;
if (typeof window !== 'undefined') {
  L = require('leaflet');
}

@Injectable()
export class MapService {

  public L: typeof L = L;

  constructor(@Inject(PLATFORM_ID) private platformId: Object) {
    if (isPlatformBrowser(platformId)) {
      this.L = require('leaflet');
    }
  }
}*/
import { Injectable, PLATFORM_ID, Inject } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';

@Injectable()
export class MapService {

  public L: any;
  public leafletLoaded: Promise<any> = Promise.resolve();
/*
  constructor(@Inject(PLATFORM_ID) private platformId: Object) {
    if (isPlatformBrowser(platformId)) {
      this.leafletLoaded = import('leaflet').then(L => this.L = L);
    }
  }*/

  constructor(@Inject(PLATFORM_ID) private platformId: Object) {
    if (isPlatformBrowser(platformId)) {
      this.leafletLoaded = import('leaflet').then(module => this.L = module.default);
    }
  }
}