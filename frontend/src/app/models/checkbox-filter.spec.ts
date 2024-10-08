import { CheckboxFilter } from './checkbox-filter';

describe('CheckboxFilter', () => {
  it('should create an instance', () => {
    expect(new CheckboxFilter(1, 'Nom', true)).toBeTruthy();
  });
});
