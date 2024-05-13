import { Component, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { ScheduleService } from '../services/schedule.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-programmation-filter',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './programmation-filter.component.html',
  styleUrl: './programmation-filter.component.css'
})
export class ProgrammationFilterComponent {
  locationsArray !: Observable<string[]>;
  eventsArray !: Observable<string[]>;

  private scheduleService = inject(ScheduleService);

  ngOnInit(): void {
    this.loadLocations();
    this.loadEvents();
  }

  // récuoération de chaque lieu ou se passe au moins un événemement
  loadLocations() {
    this.locationsArray = this.scheduleService.getPosts().pipe(
      map(artists => artists.map(artist => artist.scene || artist.lieu_rencontre) as string[]),
      map(locations => [...new Set(locations)]),
    );
  }
// récupération de chaque type d'événement
  loadEvents() {
    this.eventsArray = this.scheduleService.getPosts().pipe(
      map(artists => artists.map(artist => artist.type_evenement) as string[]),
      map(events => [...new Set(events)]),
    );
  }

}
