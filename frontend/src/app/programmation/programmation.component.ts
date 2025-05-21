import { Component, inject, OnInit, OnDestroy  } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { EventService } from '../services/event.service';
import { Program } from '../services/class';
import { Observable, BehaviorSubject, combineLatest, EMPTY, Subscription, of, switchMap,tap } from 'rxjs';
import { catchError, filter, map, take } from 'rxjs/operators';
import { SortPipe } from '../pipe/sort-by.pipe';
import { CheckboxFilter } from '../models/checkbox-filter';
import { Meta, Title } from '@angular/platform-browser';
import { RouterLink, RouterModule } from '@angular/router';
import { FilterStatusService } from '../services/filter-status.service';

@Component({
  selector: 'app-programmation',
  standalone: true,
  imports: [CommonModule, SortPipe, RouterModule,RouterLink],
  templateUrl: './programmation.component.html',
  styleUrls: ['./programmation.component.css']
})

export class ProgrammationComponent implements OnInit, OnDestroy  {
  http = inject(HttpClient);
  programs$!: Observable<Program[]>;
  filteredPrograms$!: Observable<Program[]>;
  public locationFilters!: CheckboxFilter[];
  public eventFilters!: CheckboxFilter[];
  public dateFilters!: CheckboxFilter[];
  public timeFilters!: CheckboxFilter[];
  public timeFinalFilters!: CheckboxFilter[];
  private locationFiltersApplied$ = new BehaviorSubject<string[]>([]);
  private eventFiltersApplied$ = new BehaviorSubject<string[]>([]);
  private dateFiltersApplied$ = new BehaviorSubject<string[]>([]);
  private timeFiltersApplied$ = new BehaviorSubject<string[]>([]);
  private subscription: Subscription = new Subscription();
  public timeFiltersStart!: string | null;
  public timeFiltersEnd!: string | null;
  errorMessage: string | null = null;
  loading$ = new BehaviorSubject<boolean>(true);
  error$ = new BehaviorSubject<boolean>(false);
  // Information for SEO
  constructor(private meta: Meta, private title: Title, private eventService: EventService, private filterStatusService: FilterStatusService) {
    title.setTitle("Programmation du Nation Sound Festival 2024 - Horaires et Artistes");
    meta.addTags([
      { name: 'description', content: 'Consultez la programmation complète du Nation Sound Festival 2024. Retrouvez les horaires et les artistes pour chaque scène : métal, rock, rap/urban, world et électro. Préparez votre agenda pour ne rien manquer !' }
    ]);
  }

ngOnInit(): void {
  this.loading$.next(true);
  this.error$.next(false);

  this.eventService.getEvent().pipe(
    switchMap(programs => {
      if (programs && programs.length > 0) {
        this.programs$ = of(programs);
        // D'abord charger les filtres
        return this.loadFilters().pipe(
          tap(() => {
            // Puis restaurer les filtres sauvegardés
            this.restoreFilters();
            this.applyFilters();
          })
        );
      } else {
        this.error$.next(true);
        return EMPTY;
      }
    })
  ).subscribe({
    next: () => {
      this.loading$.next(false);
    },
    error: () => {
      this.error$.next(true);
      this.loading$.next(false);
    }
  });
}
  // Unsubscribe from the observable when the component is destroyed
  ngOnDestroy() {
    // Unsubscribe when the component is destroyed
    this.subscription.unsubscribe(); 
    // Remove the meta tag when the component is destroyed
    this.meta.removeTag("name='description'");
  }

  loadFilters(): Observable<void> {
  return this.programs$.pipe(
    filter(programs => !!programs && programs.length > 0),
    take(1),
    tap(programs => {
      // Only reset filters if they do not already exist
      if (!this.locationFilters || this.locationFilters.length === 0) {
        const locations = [...new Set(programs.map(program => program.eventLocation.locationName))];
        this.locationFilters = locations.map((location, index) => ({
          id: index,
          name: location,
          isChecked: false
        }));
      }

      if (!this.eventFilters || this.eventFilters.length === 0) {
        const events = [...new Set(programs.map(program => program.type.type))];
        this.eventFilters = events.map((event, index) => ({
          id: index,
          name: event,
          isChecked: false
        }));
      }

      if (!this.dateFilters || this.dateFilters.length === 0) {
        const dates = [...new Set(programs.map(program => program.date.date))];
        this.dateFilters = dates.map((date, index) => ({
          id: index,
          name: this.eventService.formatDate(date),
          isChecked: false
        }));
      }

      // Get start times
        const times = [...new Set(programs.map(program => program.heure_debut))];
        this.timeFilters = times.map((time, index) => ({
          id: index,
          name: this.eventService.formatTime(time),
          isChecked: false
        }));

        // Get end times
        const timesEnd = [...new Set(programs.map(program => program.heure_fin))];
        this.timeFinalFilters = timesEnd.map((time, index) => ({
          id: index,
          name: this.eventService.formatTime(time),
          isChecked: false
        }));
    }),
    map(() => void 0), // Convertir en Observable<void>
    catchError(error => {
      console.error('Une erreur est survenue lors de la récupération des artistes :', error);
      return EMPTY;
    })
  );
}  

  // Restore filters from the service
  restoreFilters(): void {
    this.filterStatusService.getLocationFilters().pipe(take(1)).subscribe(filters => {
      if (filters.length > 0) {
        this.locationFilters = filters;
        this.locationFiltersApplied$.next(filters.filter(f => f.isChecked).map(f => f.name));
      }
    });

    this.filterStatusService.getEventFilters().pipe(take(1)).subscribe(filters => {
      if (filters.length > 0) {
        this.eventFilters = filters;
        this.eventFiltersApplied$.next(filters.filter(f => f.isChecked).map(f => f.name));
      }
    });

    this.filterStatusService.getDateFilters().pipe(take(1)).subscribe(filters => {
      if (filters.length > 0) {
        this.dateFilters = filters;
        this.dateFiltersApplied$.next(filters.filter(f => f.isChecked).map(f => f.name));
      }
    });

    this.filterStatusService.getTimeStart().pipe(take(1)).subscribe(time => {
      this.timeFiltersStart = time;
    });

    this.filterStatusService.getTimeEnd().pipe(take(1)).subscribe(time => {
      this.timeFiltersEnd = time;
    });
  }

  // Apply filters to the programs
  applyFilters(): void {
    this.filteredPrograms$ = combineLatest([
      this.programs$,
      this.locationFiltersApplied$,
      this.eventFiltersApplied$,
      this.dateFiltersApplied$,
      this.timeFiltersApplied$
    ]).pipe(
      map(([programs, locations, events, dates, times]) => {
        // Return all programs if no checkbox is checked
        if (!locations.length && !events.length && !dates.length && !this.timeFiltersStart && !this.timeFiltersEnd) {
          return programs; 
        }
        // Filter programs based on location
        if (locations.length) {
          programs = programs.filter(program => {
            const sceneOrLocation = program.eventLocation.locationName;
            return sceneOrLocation && locations.includes(sceneOrLocation);
          });
        }
        // Filter programs based on event type
        if (events.length) {
          programs = programs.filter(program => program.type.type && events.includes(program.type.type));
        }
        // Filter programs based on date
        if (dates.length) {
          programs = programs.filter(program => program.date.date && dates.includes(this.eventService.formatDate(program.date.date)));
        }
        //  Filter programs based on start and end time
        if (this.timeFiltersStart || this.timeFiltersEnd) {
          programs = programs.filter(program => {
            const eventStartTime = this.eventService.formatTime(program.heure_debut);
            const eventEndTime = this.eventService.formatTime(program.heure_fin);
            
            // If only the start time is defined
            if (this.timeFiltersStart && !this.timeFiltersEnd) {
              return eventStartTime >= this.timeFiltersStart;
            }
            
            // If only the end time is defined
            if (!this.timeFiltersStart && this.timeFiltersEnd) {
              return eventEndTime <= this.timeFiltersEnd;
            }
            
            // If both times are defined
            if (this.timeFiltersStart && this.timeFiltersEnd) {
              return eventStartTime >= this.timeFiltersStart && eventEndTime <= this.timeFiltersEnd;
            }
            
            return true;
          });
        }
        return programs;
      })
    );
  }

  // Check if the program's time is within the selected time range
  isTimeInRange(artistTimeStart: string, startTime: string, endTime: string): boolean {
    const [startHour, startMinute] = startTime.split(':').map(Number);
    const [endHour, endMinute] = endTime.split(':').map(Number);
    const [artistStartHour, artistStartMinute] = artistTimeStart.split(':').map(Number);
    if (artistStartHour < startHour || artistStartHour > endHour) {
        return false; 
    }
    if (artistStartHour === startHour && artistStartMinute < startMinute) {
        return false; 
    }
    if (artistStartHour === endHour && artistStartMinute > endMinute) {
        return false; 
    }

    return true;
  }

  // Filters change handlers
  onLocationFilterChange(filter: CheckboxFilter): void {
    filter.isChecked = !filter.isChecked;
    const selectedLocations = this.locationFilters
      .filter(f => f.isChecked)
      .map(f => f.name);
    this.locationFiltersApplied$.next(selectedLocations);
    this.filterStatusService.setLocationFilters(this.locationFilters);
    this.applyFilters();
  }

  onEventFilterChange(filter: CheckboxFilter): void {
    filter.isChecked = !filter.isChecked;
    const selectedEvents = this.eventFilters
      .filter(f => f.isChecked)
      .map(f => f.name);
    this.eventFiltersApplied$.next(selectedEvents);
    this.filterStatusService.setEventFilters(this.eventFilters);
    this.applyFilters();
  }

  onDateFilterChange(filter: CheckboxFilter): void {
    filter.isChecked = !filter.isChecked;
    const selectedDates = this.dateFilters
      .filter(f => f.isChecked)
      .map(f => f.name);
    this.dateFiltersApplied$.next(selectedDates);
    this.filterStatusService.setDateFilters(this.dateFilters);
    this.applyFilters();
  }

  onTimeFilterChange(filter: CheckboxFilter): void {
    filter.isChecked = !filter.isChecked;
    const selectedTimes = this.timeFilters
      .filter(f => f.isChecked)
      .map(f => f.name);
    this.timeFiltersApplied$.next(selectedTimes);
    this.applyFilters();
  }

  // Start time filter management
  onTimeStartChange(event: Event): void {
    const selectElement = event.target as HTMLSelectElement;
    const selectedTime = selectElement.value;
    
    if (selectedTime === '') {
      this.timeFiltersStart = null;
    } else {
      this.timeFiltersStart = selectedTime;
      if (this.timeFiltersEnd && !this.validateTime()) {
        this.timeFiltersStart = null;
        selectElement.value = '';
        this.errorMessage = "L'heure de début ne peut pas être supérieure à l'heure de fin.";
      } else {
        this.errorMessage = null;
      }
    }
    this.filterStatusService.setTimeStart(this.timeFiltersStart);
    this.applyFilters();
  }

  // End time filter management
  onTimeEndChange(event: Event): void {
    const selectElement = event.target as HTMLSelectElement;
    const selectedTime = selectElement.value;
    
    if (selectedTime === '') {
      this.timeFiltersEnd = null;
    } else {
      this.timeFiltersEnd = selectedTime;
      if (this.timeFiltersStart && !this.validateTime()) {
        this.timeFiltersEnd = null;
        selectElement.value = '';
        this.errorMessage = "L'heure de fin ne peut pas être inférieure à l'heure de début.";
      } else {
        this.errorMessage = null;
      }
    }
    this.filterStatusService.setTimeEnd(this.timeFiltersEnd);
    this.applyFilters();
  }

  // Validation that the choice of start time does not exceed the end time and vice versa
  validateTime(): boolean {
    if (this.timeFiltersStart && this.timeFiltersEnd) {
      const startParts = this.timeFiltersStart.split(':').map(Number);
      const endParts = this.timeFiltersEnd.split(':').map(Number);
      const startMinutes = startParts[0] * 60 + startParts[1];
      const endMinutes = endParts[0] * 60 + endParts[1];
      return startMinutes <= endMinutes;
    }
    return true;
  }
}