function getPAKData(ordinal = true) {

  const NGROUPS = 3,
    MAXLINES = 15,
    MAXSEGMENTS = 20,
    MAXCATEGORIES = 4,
    MINTIME = new Date(1800,1,1);

  const nCategories = 4,
    categoryLabels = ['Cartas','Outros Documentos','Eventos','Pessoas'];

  const data = {
    'documentos' :{
      label: 'Documentos',
      data:[
          {
              label: 'Cartas',
              data: [
                  {
                      timeRange: ['12/09/1857', '12/09/1857'],
                      val: 'Cartas'
                  },
                  {
                      timeRange: ['01/05/1865', '01/05/1865'],
                      val: 'Cartas'
                  }
              ]
          },
          {
              label: 'Outros documentos',
              data: [
                  {
                      timeRange: ['12/09/1857', '12/09/1857'],
                      val: 'Outros documentos'
                  },
                  {
                      timeRange: ['01/05/1865', '01/05/1865'],
                      val: 'Outros documentos'
                  }
              ]
          }
      ]
    },
    'eventos' :{
      label: 'Eventos',
      data:[
          {
              label: 'LE',
              data: [
                  {
                      timeRange: ['04/18/1857', '04/18/1857'],
                      val: 'Livro'
                  },
              ]
          },
          {
              label: 'XYZ',
              data: [
                  {
                      timeRange: ['12/10/1866', '12/10/1866'],
                      val: 'Viagem'
                  }
              ]
          }
      ]
    },
    'pessoas' :{
      label: 'Pessoas',
      data:[
          {
              label: 'Allan Kardec',
              data: [
                  {
                      timeRange: ['03/10/1804', '03/31/1869'],
                      val: 'Pessoa'
                  },
              ]
          },
          {
              label: 'Outra pessoa',
              data: [
                  {
                      timeRange: ['12/05/1830', '01/05/1865'],
                      val: 'Pessoa'
                  }
              ]
          }
      ]
    },
  }

  return ['documentos','eventos','pessoas'].map(i => ({
    group: data[i].label,
    data: data[i].data
  }));


  function getGroupData() {

    /*
    return [...Array(Math.ceil(Math.random()*MAXLINES)).keys()].map(i => ({
      label: 'label' + (i+1),
      data: getSegmentsData()
    }));

    //

    function getSegmentsData() {
      const nSegments = Math.ceil(Math.random()*MAXSEGMENTS),
        segMaxLength = Math.round(((new Date())-MINTIME)/nSegments);
      let runLength = MINTIME;

      return [...Array(nSegments).keys()].map(i => {
        const tDivide = [Math.random(), Math.random()].sort(),
          start = new Date(runLength.getTime() + tDivide[0]*segMaxLength),
          end = new Date(runLength.getTime() + tDivide[1]*segMaxLength);

        runLength = new Date(runLength.getTime() + segMaxLength);

        return {
          timeRange: [start, end],
          val: ordinal ? categoryLabels[Math.ceil(Math.random()*nCategories)] : Math.random()
          //labelVal: is optional - only displayed in the labels
        };
      });

    }

     */
  }
}