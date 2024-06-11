import { ComponentFixture, TestBed } from '@angular/core/testing';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { of, BehaviorSubject } from 'rxjs';
import { ProgrammationComponent } from './programmation.component';
import { ScheduleService } from '../services/schedule.service';
import { Meta, Title } from '@angular/platform-browser';

describe('ProgrammationComponent', () => {
  // Déclaration des variables pour le test
  // Define the variables for the test
  let component: ProgrammationComponent;
  let fixture: ComponentFixture<ProgrammationComponent>;
  let scheduleService: ScheduleService;
  let meta: Meta;
  let title: Title;

  // Avant chaque test, nous configurons le module de test
  // Before each test, we set up the test module
  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [HttpClientTestingModule, ProgrammationComponent],
      providers: [ ScheduleService,
         Meta,
        Title, ]
    })
    .compileComponents();
  });

  // Avant chaque test, nous créons une nouvelle instance du composant et du service
  // Before each test, we create a new instance of the component and the service
  beforeEach(() => {
    fixture = TestBed.createComponent(ProgrammationComponent);
    component = fixture.componentInstance;
    meta = TestBed.inject(Meta);
    title = TestBed.inject(Title);
    scheduleService = TestBed.inject(ScheduleService);
    fixture.detectChanges();
  });
  // Test pour vérifier que le composant est bien créé
  // Test to check if the component is created
  it('should create', () => {
    expect(component).toBeTruthy();
  });

    // Test pour vérifier que le titre de la page est correctement défini
  // Test to check if the page title is correctly set
  it('should set the page title', () => {
    expect(title.getTitle()).toBe('Programmation du Nation Sound Festival 2024 - Horaires et Artistes');
  });

  // Test pour vérifier que la description méta est correctement définie
  // Test to check if the meta description is correctly set
  it('should set the meta description', () => {
    // description attendue
    // expected description
    const expectedDescription = "Consultez la programmation complète du Nation Sound Festival 2024. Retrouvez les horaires et les artistes pour chaque scène : métal, rock, rap/urban, world et électro. Préparez votre agenda pour ne rien manquer !";

    // Récupération de la balise meta description
    // Get the meta description tag
    const actualDescription = meta.getTag('name=description');

    // Vérification que la balise existe
    // Check if the tag exists
    expect(actualDescription).toBeTruthy();

    // Vérification que le contenu de la balise est correct
    // Check if the content of the tag is correct
    if (actualDescription) {
      expect(actualDescription.content).toBe(expectedDescription);
    }
  });

  // Test pour vérifier que les artistes sont bien chargés lors de l'initialisation
  // Test to check if the artists are loaded during initialization
  it('should load artists on init', () => {
    spyOn(scheduleService, 'getPosts').and.returnValue(of([]));
    component.ngOnInit();
    expect(scheduleService.getPosts).toHaveBeenCalled();
  });

  // Test pour vérifier que les filtres sont bien appliqués lors de l'initialisation
  // Test to check if the filters are applied during initialization
  it('should apply filters on init', () => {
    spyOn(component, 'applyFilters');
    component.ngOnInit();
    expect(component.applyFilters).toHaveBeenCalled();
  });

  // Test pour vérifier que la méthode 'getPosts' du service 'scheduleService' est appelée lors de l'appel de la méthode 'loadArtists'
  it('should call getPosts when loadArtists is called', () => {
    spyOn(scheduleService, 'getPosts').and.returnValue(of([]));
    component.loadArtists();
    expect(scheduleService.getPosts).toHaveBeenCalled();
  });

});