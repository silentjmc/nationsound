import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { Observable, forkJoin, map, merge, pipe } from 'rxjs';
import { Artist } from './class';

// Service for connect to API of the list of artists
const BASE_URL = 'https://jmcarre.go.yj.fr/nationsound/nationsoundbe/wp-json/wp/v2/programmations?page=1&per_page=100&acf_format=standard';
const BASE_URL_P2 = 'https://jmcarre.go.yj.fr/nationsound/nationsoundbe/wp-json/wp/v2/programmations?page=2&per_page=100&acf_format=standard';
@Injectable({
  providedIn: 'root'
})
export class ScheduleService  {
  private http = inject(HttpClient)
  constructor() { }

getPosts(): Observable<Artist[]> {
  const pageOne$ = this.http.get(BASE_URL);
  const pageTwo$ = this.http.get(BASE_URL_P2);
  // copmbiner les deux requêtes de l'API wordpress car chaque requete est limité à 100 résultats
  return forkJoin([pageOne$, pageTwo$])
    .pipe(
      map(([pageOne, pageTwo]) => {
        // Convertir les réponses en tableaux
        const arrayOne = Array.isArray(pageOne) ? pageOne : [pageOne];
        const arrayTwo = Array.isArray(pageTwo) ? pageTwo : [pageTwo];

        // Extraire les propriétés utilisées pour l'affichage'
        const combinedArray = [
          ...arrayOne.map(item => ({  id: item.id, 
                                      name: item.title.rendered, 
                                      description: item.acf.description_artistes, 
                                      type_musique: item.acf.type_musique,
                                      photo_artiste: item.acf.photo_artistes,
                                      date: item.acf.date_concert, 
                                      heure_debut: item.acf.heure_debut_concert, 
                                      heure_fin: item.acf.heure_fin_concert,
                                      type_evenement: item.acf.type_evenement,
                                      scene : item.acf.scene,
                                      lieu_rencontre: item.acf.lieu_rencontre                                      
                                      })),
          ...arrayTwo.map(item => ({  id: item.id, 
                                      name: item.title.rendered, 
                                      description: item.acf.description_artistes, 
                                      type_musique: item.acf.type_musique,
                                      photo_artiste: item.acf.photo_artistes,
                                      date: item.acf.date_concert, 
                                      heure_debut: item.acf.heure_debut_concert, 
                                      heure_fin: item.acf.heure_fin_concert,
                                      type_evenement: item.acf.type_evenement,
                                      scene : item.acf.scene,
                                      lieu_rencontre: item.acf.lieu_rencontre
                                      })),
        ];
        return combinedArray;
      })
    );
  }


}