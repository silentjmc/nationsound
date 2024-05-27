import { Component, inject, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { ScheduleService } from '../services/schedule.service';
import { Artist, CustomEvent } from '../services/class';
import { Observable, BehaviorSubject, combineLatest, of, forkJoin, EMPTY, Subscription } from 'rxjs';
import { catchError, filter, map, switchMap, take } from 'rxjs/operators';
import { SortPipe } from '../pipe/sort-by.pipe';
import { CheckboxFilter } from '../models/checkbox-filter';
import { Meta, Title } from '@angular/platform-browser';

@Component({
  selector: 'app-programmation',
  standalone: true,
  imports: [CommonModule, SortPipe],
  templateUrl: './programmation.component.html',
  styleUrls: ['./programmation.component.css']
})

export class ProgrammationComponent implements OnInit {

  constructor(private meta: Meta, private title: Title) {
    title.setTitle("Programmation du Nation Sound Festival 2024 - Horaires et Artistes");

    meta.addTags([
      { name: 'description', content: 'Consultez la programmation complète du Nation Sound Festival 2024. Retrouvez les horaires et les artistes pour chaque scène : métal, rock, rap/urban, world et électro. Préparez votre agenda pour ne rien manquer !' }
    ]);
  }


  http = inject(HttpClient);
  artists$!: Observable<Artist[]>;
  filteredArtists$!: Observable<Artist[]>;
  public locationFilters!: CheckboxFilter[];
  public eventFilters!: CheckboxFilter[];
  public dateFilters!: CheckboxFilter[];
  public timeFilters!: CheckboxFilter[];
  public timeFinalFilters!: CheckboxFilter[];
  private locationFiltersApplied$ = new BehaviorSubject<string[]>([]);
  private eventFiltersApplied$ = new BehaviorSubject<string[]>([]);
  private dateFiltersApplied$ = new BehaviorSubject<string[]>([]);
  private scheduleService = inject(ScheduleService);
  private subscription: Subscription = new Subscription();
  public timeFiltersStart!: string | null;
  public timeFiltersEnd!: string | null;
  errorMessage: string | null = null;


  ngOnInit(): void {
    this.loadArtists();
    this.loadFilters();
    this.applyFilters();
  }

  ngOnDestroy() {
    this.subscription.unsubscribe(); // Unsubscribe when the component is destroyed
  }

  loadArtists() {
    this.artists$ = this.scheduleService.getPosts().pipe(
      catchError(error => {
        console.error('Une erreur est survenue lors de la récupération des artistes :', error);
        return EMPTY; // Retourne un observable vide en cas d'erreur
      })
    );
  }

  loadFilters() {
    if (!this.artists$) {
      console.error("No data available in this.artists$");
      return; // Exit the function if no data is available
    }

    this.subscription = this.artists$.pipe(
      filter(artists => !!artists),
      map(artists => {
        const locations = [...new Set(artists.map(artist => artist.scene || artist.lieu_rencontre))];
        this.locationFilters = locations.map((location, index) => ({
          id: index,
          name: location,
          isChecked: false
        } as CheckboxFilter));

        const events = [...new Set(artists.map(artist => artist.type_evenement))];
        this.eventFilters = events.map((event, index) => ({
          id: index,
          name: event,
          isChecked: false
        } as CheckboxFilter));

        const dates = [...new Set(artists.map(artist => artist.date))];
        this.dateFilters = dates.map((date, index) => ({
          id: index,
          name: date,
          isChecked: false
        }));

        const times = [...new Set(artists.map(artist => artist.heure_debut))];
        this.timeFilters = times.map((time, index) => ({
          id: index,
          name: time,
          isChecked: false
        }));

        const timesEnd = [...new Set(artists.map(artist => artist.heure_fin))];
        this.timeFinalFilters = timesEnd.map((time, index) => ({
          id: index,
          name: time,
          isChecked: false
      }));

      }),
      catchError(error => {
        console.error('Une erreur est survenue lors de la récupération des artistes :', error);
        return EMPTY; // Retourne un observable vide en cas d'erreur
      })
    ).subscribe();
  }
/*
  isTimeInRange(artistTimeStart: string, startTime: string, endTime: string): boolean {
    const start = this.convertToMinutes(startTime);
    let end = this.convertToMinutes(endTime);
    let artistStart = this.convertToMinutes(artistTimeStart);
  
    if (endTime === '00:00') {
      // No conversion needed, consider it as the end of the day
    }
    return artistStart >= start && artistStart <= end;
  }
*/
/*
  isTimeInRange(artistTimeStart: string, startTime: string, endTime: string): boolean {
    const start = this.convertToMinutes(startTime);
    const end = this.convertToMinutes(endTime);
    const artistStart = this.convertToMinutes(artistTimeStart);

    return artistStart >= start && artistStart <= end;
}
*/

isTimeInRange(artistTimeStart: string, startTime: string, endTime: string): boolean {
  // Diviser les heures et les minutes pour les comparer
  const [startHour, startMinute] = startTime.split(':').map(Number);
  const [endHour, endMinute] = endTime.split(':').map(Number);
  const [artistStartHour, artistStartMinute] = artistTimeStart.split(':').map(Number);

  // Comparaison des heures et des minutes
  if (artistStartHour < startHour || artistStartHour > endHour) {
      return false; // L'heure de début de l'artiste est en dehors de la plage horaire
  }
  if (artistStartHour === startHour && artistStartMinute < startMinute) {
      return false; // L'heure de début de l'artiste est avant l'heure de début
  }
  if (artistStartHour === endHour && artistStartMinute > endMinute) {
      return false; // L'heure de début de l'artiste est après l'heure de fin
  }

  return true; // L'heure de début de l'artiste est dans la plage horaire spécifiée
}


/*
  convertToMinutes(time: string): number {
    const [hours, minutes] = time.split(':').map(Number);
    return hours * 60 + minutes;
  }
  */
/*
  applyFilters() {
    this.filteredArtists$ = combineLatest([
      this.artists$,
      this.locationFiltersApplied$,
      this.eventFiltersApplied$,
      this.dateFiltersApplied$,
    ]).pipe(
      map(([artists, locations, events, dates]) => {
        if (!locations.length && !events.length && !dates.length && !this.timeFiltersStart && !this.timeFiltersEnd) {
          return artists; // Retourner tous les artistes si aucune case à cocher n'est cochée
        }

        if (locations.length) {
          artists = artists.filter(artist => {
            const sceneOrLocation = artist.scene || artist.lieu_rencontre;
            return sceneOrLocation && locations.includes(sceneOrLocation);
          });
        }
        if (events.length) {
          artists = artists.filter(artist => artist.type_evenement && events.includes(artist.type_evenement));
        }
        if (dates.length) {
          artists = artists.filter(artist => artist.date && dates.includes(artist.date));
        }

        if (this.timeFiltersStart || this.timeFiltersEnd) {
          artists = artists.filter(artist => {
            const artistTimeStart = artist.heure_debut;
            const artistTimeEnd = artist.heure_fin;
            const startTime = this.timeFiltersStart ? this.timeFiltersStart : '00:00';
            const endTime = this.timeFiltersEnd ? this.timeFiltersEnd : '23:59';
            return artistTimeStart >= startTime && artistTimeEnd <= endTime;
          });
        }

        return artists;
      })
    );
  }
*/
applyFilters() {
  this.filteredArtists$ = combineLatest([
    this.artists$,
    this.locationFiltersApplied$,
    this.eventFiltersApplied$,
    this.dateFiltersApplied$,
  ]).pipe(
    map(([artists, locations, events, dates]) => {
      if (!locations.length && !events.length && !dates.length && !this.timeFiltersStart && !this.timeFiltersEnd) {
        return artists; // Retourner tous les artistes si aucune case à cocher n'est cochée
      }

      if (locations.length) {
        artists = artists.filter(artist => {
          const sceneOrLocation = artist.scene || artist.lieu_rencontre;
          return sceneOrLocation && locations.includes(sceneOrLocation);
        });
      }
      if (events.length) {
        artists = artists.filter(artist => artist.type_evenement && events.includes(artist.type_evenement));
      }
      if (dates.length) {
        artists = artists.filter(artist => artist.date && dates.includes(artist.date));
      }
/*
      if (this.timeFiltersStart || this.timeFiltersEnd) {
        artists = artists.filter(artist => {
            const artistTimeStart = artist.heure_debut;
            const startTime = this.timeFiltersStart ? this.timeFiltersStart : '00:00';
            const endTime = this.timeFiltersEnd ? this.timeFiltersEnd : '23:59';
            return this.isTimeInRange(artistTimeStart, startTime, endTime);
        });
      }
*/
  if (this.timeFiltersStart || this.timeFiltersEnd) {
    artists = artists.filter(artist => {
        const artistTimeStart = artist.heure_debut;
        const artistTimeEnd = artist.heure_fin;
        const startTime = this.timeFiltersStart ? this.timeFiltersStart : '00:01';
        const endTime = this.timeFiltersEnd ? this.timeFiltersEnd : '23:59';
        return this.isTimeInRange(artistTimeStart, startTime, endTime) && this.isTimeInRange(artistTimeEnd, startTime, endTime);
      
    });
}

      return artists;
    })
  );
}





  locationChanged(filter: string | null): void {
    const currentFilters = this.locationFiltersApplied$.getValue();
    if (filter) {
      const idx = currentFilters.indexOf(filter);
      if (idx >= 0) {
        currentFilters.splice(idx, 1);
      } else {
        currentFilters.push(filter);
      }
      this.locationFiltersApplied$.next(currentFilters);
    }
  }

  eventChanged(filter: string | null): void {
    const currentFilters = this.eventFiltersApplied$.getValue();
    if (filter) {
      const idx = currentFilters.indexOf(filter);
      if (idx >= 0) {
        currentFilters.splice(idx, 1);
      } else {
        currentFilters.push(filter);
      }
      this.eventFiltersApplied$.next(currentFilters);
    }
  }

  dateChanged(filter: string | null): void {
    const currentFilters = this.dateFiltersApplied$.getValue();
    if (filter) {
      const idx = currentFilters.indexOf(filter);
      if (idx >= 0) {
        currentFilters.splice(idx, 1);
      } else {
        currentFilters.push(filter);
      }
      this.dateFiltersApplied$.next(currentFilters);
    }
  }

  onTimeStartChange(event: Event): void {
    const selectElement = event.target as HTMLSelectElement;
    const lastValueTimeStart = this.timeFiltersStart;
    this.timeFiltersStart = selectElement.value;
    if (!this.validateTime()) {
      this.timeFiltersStart = lastValueTimeStart;
      selectElement.value = lastValueTimeStart as string;
      this.errorMessage = "L'heure de début ne peut pas être supérieure à l'heure de fin.";
      console.error(this.errorMessage);
    } else {
      this.errorMessage = null;  // Réinitialiser le message d'erreur
    }
    this.applyFilters();
  }

  onTimeEndChange(event: Event): void {
    const selectElement = event.target as HTMLSelectElement;
    const lastValueTimeEnd = this.timeFiltersEnd;
    this.timeFiltersEnd = selectElement.value;
    if (!this.validateTime()) {
      this.timeFiltersEnd = lastValueTimeEnd;
      selectElement.value = lastValueTimeEnd as string;
      this.errorMessage = "L'heure de fin ne peut pas être inférieure à l'heure de début.";
      console.error(this.errorMessage);
    } else {
      this.errorMessage = null;  // Réinitialiser le message d'erreur
    }
    this.applyFilters();
  }

  validateTime(): boolean {
    if (this.timeFiltersStart && this.timeFiltersEnd) {
      return this.timeFiltersStart <= this.timeFiltersEnd;
    }
    return true;
  }
}



/*
export class ProgrammationComponent implements OnInit {
  http = inject(HttpClient);
  artists$!: Observable<Artist[]>;
  filteredArtists$!: Observable<Artist[]>;
  public locationFilters!: CheckboxFilter[];
  public eventFilters!: CheckboxFilter[];
  private locationFiltersApplied$ = new BehaviorSubject<string[]>([]);
  private eventFiltersApplied$ = new BehaviorSubject<string[]>([]);
  private scheduleService = inject(ScheduleService);
  private subscription: Subscription = new Subscription();
  dateFilters!: CheckboxFilter[];
  timeFilters!: CheckboxFilter[];
  private dateFiltersApplied$ = new BehaviorSubject<string[]>([]);
  public timeFiltersStart!: string | null;
  public timeFiltersEnd!: string | null;

  ngOnInit(): void {
    this.loadArtists();
    this.loadFilters();
    this.applyFilters();
  }


  loadArtists() {
    this.artists$ = this.scheduleService.getPosts().pipe(
      catchError(error => {
        console.error('Une erreur est survenue lors de la récupération des artistes :', error);
        return EMPTY; // Retourne un observable vide en cas d'erreur
      })
    );
  }
  

  loadFilters() {
    if (!this.artists$) {
      console.error("No data available in this.artists$");
      return; // Exit the function if no data is available
    }

    this.subscription = this.artists$.pipe(
      filter(artists => !!artists),
      map(artists => {
        // console.log(artists)
        const locations = [...new Set(artists.map(artist => artist.scene || artist.lieu_rencontre))];
        this.locationFilters = locations.map((location, index) => ({
          id: index,
          name: location,
          isChecked: false
        } as CheckboxFilter));
  
        const events = [...new Set(artists.map(artist => artist.type_evenement))];
        this.eventFilters = events.map((event, index) => ({
          id: index,
          name: event,
          isChecked: false
        } as CheckboxFilter));

        const dates = [...new Set(artists.map(artist => artist.date))];
        this.dateFilters = dates.map((date, index) => ({
          id: index,
          name: date,
          isChecked: false
        }));

        const times = [...new Set(artists.map(artist => artist.heure_debut))];
        this.timeFilters = times.map((time, index) => ({
          id: index,
          name: time,
          isChecked: false
        }));


      }),
      catchError(error => {
        console.error('Une erreur est survenue lors de la récupération des artistes :', error);
        return EMPTY; // Retourne un observable vide en cas d'erreur
      })
    ).subscribe();
  }

  ngOnDestroy() {
    this.subscription.unsubscribe(); // Unsubscribe when the component is destroyed
  }

applyFilters() {
  this.filteredArtists$ = combineLatest([
    this.artists$,
    this.locationFiltersApplied$,
    this.eventFiltersApplied$,
    this.dateFiltersApplied$,
  ]).pipe(
      map(([artists, locations, events, dates]) => {
        if (!locations.length && !events.length && !dates.length && !this.timeFiltersStart && !this.timeFiltersEnd) {
        return artists; // Retourner tous les artistes si aucune case à cocher n'est cochée
      }

      if (locations.length) {
        artists = artists.filter(artist => {
          const sceneOrLocation = artist.scene || artist.lieu_rencontre;
          return sceneOrLocation && locations.includes(sceneOrLocation);
        });
      }
      if (events.length) {
        artists = artists.filter(artist => artist.type_evenement && events.includes(artist.type_evenement));
      }
      if (dates.length) {
        artists = artists.filter(artist => artist.date && dates.includes(artist.date));
      }

      if (this.timeFiltersStart || this.timeFiltersEnd) {
        artists = artists.filter(artist => {
          const artistTime = artist.heure_debut;
          const startTime = this.timeFiltersStart ? this.timeFiltersStart : '00:00';
          const endTime = this.timeFiltersEnd ? this.timeFiltersEnd : '23:59';
          return artistTime >= startTime && artistTime <= endTime;
        });
      }

      return artists;
    })
  );
}

  locationChanged(filter: string | null): void {
    const currentFilters = this.locationFiltersApplied$.getValue();
    if (filter) {
      const idx = currentFilters.indexOf(filter);
      if (idx >= 0) {
        currentFilters.splice(idx, 1);
      } else {
        currentFilters.push(filter);
      }
      this.locationFiltersApplied$.next(currentFilters);
    }
  }
  
  eventChanged(filter: string | null): void {
    const currentFilters = this.eventFiltersApplied$.getValue();
    if (filter) {
      const idx = currentFilters.indexOf(filter);
      if (idx >= 0) {
        currentFilters.splice(idx, 1);
      } else {
        currentFilters.push(filter);
      }
      this.eventFiltersApplied$.next(currentFilters);
    }
  }
  
  dateChanged(filter: string | null): void {
    const currentFilters = this.dateFiltersApplied$.getValue();
    if (filter) {
      const idx = currentFilters.indexOf(filter);
      if (idx >= 0) {
        currentFilters.splice(idx, 1);
      } else {
        currentFilters.push(filter);
      }
      this.dateFiltersApplied$.next(currentFilters);
    }
  }

timeStartChanged(filter: string | null): void {
  this.timeFiltersStart = filter;
  this.applyFilters();
  
}

  timeEndChanged(filter: string | null): void {
    this.timeFiltersEnd = filter;
    this.applyFilters();
  }

  

}*/
