import { Component, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { ScheduleService } from '../services/schedule.service';
import { Artist } from '../services/class';
import { Observable} from 'rxjs';
import { ProgrammationFilterComponent } from '../programmation-filter/programmation-filter.component';
import { SortPipe } from '../pipe/sort-by.pipe';

@Component({
  selector: 'app-programmation',
  standalone: true,
  imports: [CommonModule, ProgrammationFilterComponent, SortPipe],
  templateUrl: './programmation.component.html',
  styleUrl: './programmation.component.css'
})
export class ProgrammationComponent {
  http = inject(HttpClient);
  artists$!: Observable<Artist[]>;
  scenesArray!: Observable<string[]>;
  locationFilter$!: Observable<any[]>;
  private scheduleService = inject(ScheduleService);

  ngOnInit(): void {
    this.loadArtists();
  }

  loadArtists() {
    this.artists$ = this.scheduleService.getPosts();

  }



/*
loadScenes() {
  this.scenesArray = this.scheduleService.getPosts().pipe(
    map(artists => artists.map(artist => artist.scene || artist.lieu_rencontre) as string[]),
    map(scenes => [...new Set(scenes)]),
  );
}
*/
}
