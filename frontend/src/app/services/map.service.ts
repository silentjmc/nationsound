import { Injectable, PLATFORM_ID, Inject, inject } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';
import { HttpClient} from '@angular/common/http';
import { Observable, map, shareReplay,} from 'rxjs';
import { Poi } from './class';
import { environment } from '../../environments/environment';

const BASE_URL = 'http://127.0.0.1:8000/api/eventLocation'
@Injectable()
export class MapService {
  private http = inject(HttpClient)
  poiEa$!: Observable<Poi[]>;

  constructor(@Inject(PLATFORM_ID) private platformId: Object) {
    this.poiEa$ = this.getLocation();
    if (isPlatformBrowser(platformId)) {
      this.leafletLoaded = import('leaflet').then(module => this.L = module.default);
    }
  }

  getLocation():Observable<Poi[]> {
    return this.http.get<Poi[]>(BASE_URL).pipe(
      map((response: any[]) => {
        return response.map(event => ({
          id: event.id,
          name: event.locationName,
          type: event.typeLocation.type,
          text: event.description,
          lat: event.latitude,
          lon: event.longitude,
          iconUrl: `${environment.apiUrl}/uploads/locations/${event.typeLocation.symbol}`
        }));
      }),
      shareReplay(1)
    );
  }

  public L: any;
  public leafletLoaded: Promise<any> = Promise.resolve();
    
}