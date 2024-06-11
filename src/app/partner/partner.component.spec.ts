import { TestBed, ComponentFixture } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { PLATFORM_ID } from '@angular/core';
import { Meta, Title } from '@angular/platform-browser';
import { PartnerComponent } from './partner.component';
import { HttpClientTestingModule } from '@angular/common/http/testing';
import { PartnerswpService } from '../services/partnerswp.service';
import { of } from 'rxjs';


describe('PartnerComponent', () => {
  // Déclaration des variables qui seront utilisées dans les tests
  // Declaration of the variables that will be used in the tests
  let component: PartnerComponent;
  let fixture: ComponentFixture<PartnerComponent>;
  let meta: Meta;
  let title: Title;
  let partnerswpService: PartnerswpService;

  // Avant chaque test, nous configurons le module de test et créons une instance du composant à tester
  // Before each test, we set up the test module and create an instance of the component to test
  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ HttpClientTestingModule, PartnerComponent],
      providers: [ PartnerswpService,
        Meta,
        Title,
        { provide: PLATFORM_ID, useValue: 'browser' }
      ]
    }).compileComponents();
  });

  // Avant chaque test, nous créons une nouvelle instance du composant et du service
  // Before each test, we create a new instance of the component and the service
  beforeEach(() => {
    fixture = TestBed.createComponent(PartnerComponent);
    component = fixture.componentInstance;
    meta = TestBed.inject(Meta);
    title = TestBed.inject(Title);
    partnerswpService = TestBed.inject(PartnerswpService); 
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
    expect(title.getTitle()).toBe('Partenaires du Nation Sound Festival 2024');
  });

  // Test pour vérifier que la description méta est correctement définie
  // Test to check if the meta description is correctly set
  it('should set the meta description', () => {
    // description attendue
    // expected description
    const expectedDescription = "Découvrez nos partenaires qui rendent le Nation Sound Festival 2024 possible. Merci à eux pour leur soutien et leur engagement à faire de cet événement un succès !";

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

  it('should call getPosts when loadPartners is called', () => {
    spyOn(partnerswpService, 'getPosts').and.returnValue(of([]));
    component.loadPartners();
    expect(partnerswpService.getPosts).toHaveBeenCalled();
  });
});