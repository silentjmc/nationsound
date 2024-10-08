import { ComponentFixture, TestBed } from '@angular/core/testing';
import { MapComponent } from './map.component';
import { MapService } from '../services/map.service';

describe('MapComponent', () => {
  let component: MapComponent;
  let fixture: ComponentFixture<MapComponent>;
  let mapService: MapService;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ MapComponent ],
      providers: [ MapService ]
    })
    .compileComponents();
    mapService = TestBed.inject(MapService);
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(MapComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should load Leaflet library', (done) => {
    mapService.leafletLoaded.then(() => {
      expect(mapService.L).toBeDefined();
      done();
    });
  });
});