import { Injectable, inject } from '@angular/core';
import { HttpClient} from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Observable } from 'rxjs';
import { Artist } from './class';
import { map } from 'rxjs/operators';


const BASE_URL_ARTISTLIST = `${environment.apiUrl}/api/artistsList`;
const BASE_URL_ARTIST = `${environment.apiUrl}/api/artist`;
@Injectable({
  providedIn: 'root'
})
export class ArtistService {
  private http = inject(HttpClient)
  artists$!: Observable<Artist[]>; 
  constructor() { }

  getAllArtists(): Observable<Artist[]> {
    return this.http.get<Artist[]>(BASE_URL_ARTISTLIST).pipe(
      map((artists: Artist[]) => {
        return artists.map(artist => ({
          ...artist,
          imageArtist: `${environment.apiUrl}/uploads/artists/${artist.imageArtist}`,
          thumbnail: `${environment.apiUrl}/uploads/artists/${artist.thumbnail}`
        }));
      })
    );
  }

  getArtist(id: number): Observable<Artist> {
    return this.http.get<Artist>(`${BASE_URL_ARTIST}/${id}`).pipe(
      map((artist: Artist) => ({
        ...artist,
        imageArtist: `${environment.apiUrl}/uploads/artists/${artist.imageArtist}`,
        thumbnail: `${environment.apiUrl}/uploads/artists/${artist.thumbnail}`
      }))
    );
  }
}
