import { Injectable, PLATFORM_ID, Inject } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';

@Injectable()
export class MapService {

  public L: any;
  public leafletLoaded: Promise<any> = Promise.resolve();
  
  // Chargement de la librairie Leaflet
  // Loading the Leaflet library
  constructor(@Inject(PLATFORM_ID) private platformId: Object) {
    if (isPlatformBrowser(platformId)) {
      this.leafletLoaded = import('leaflet').then(module => this.L = module.default);
    }
  }
}