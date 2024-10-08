import {Pipe, PipeTransform} from '@angular/core';

@Pipe({
  name: 'sortBy',
  standalone: true,
  pure: false // Set pure to false to allow for re-execution of the pipe when the data changes
})

/*
export class SortPipe implements PipeTransform {
  transform(array: Array<any> | null, args: any): Array<any> | null {

    if (array === null) {
      return null;
    }
   
    if (array.length === 0) {
      return array;
    }

    if (array !== undefined) {
        let keys!: string[], order: number;
        if(typeof args == 'string'){ // use default sort criteria
            keys = [args];
            order = 1;
        }

        if(keys.length > 0){
            array.sort((a: any, b: any) => {
                if (a[keys[0]] < b[keys[0]]) {
                    return -1 * order;
                } else if (a[keys[0]] > b[keys[0]]) {
                    return 1 * order;
                } else {
                    return 0;
                }
            });
        }
    }
    return array;
    }
}
*/

export class SortPipe implements PipeTransform {
  transform(array: Array<any> | null, args: any): Array<any> | null {
    if (array === null || array === undefined || array.length === 0 || typeof args !== 'string') {
      return array;
    }

    const keys = [args];
    const order = 1;

    array.sort((a: any, b: any) => {
      if (a[keys[0]] < b[keys[0]]) {
        return -1 * order;
      } else if (a[keys[0]] > b[keys[0]]) {
        return 1 * order;
      } else {
        return 0;
      }
    });

    return array;
  }
}