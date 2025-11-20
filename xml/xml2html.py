import xml.etree.ElementTree as ET

# Clase para generar el HTML
class Html:
    def __init__(self, outFile):
        self.out = outFile

    def prologo(self):
        self.out.write('<!DOCTYPE html>\n')
        self.out.write('<html lang="es">\n')
        self.out.write('<head>\n')
        self.out.write('    <meta charset="UTF-8">\n')
        self.out.write('    <meta name="viewport" content="width=device-width, initial-scale=1.0">\n')
        self.out.write('    <title>Información del Circuito de Le Mans</title>\n')
        self.out.write('    <link rel="stylesheet" type="text/css" href="estilo/estilo.css">\n')
        self.out.write('</head>\n')
        self.out.write('<body>\n')
        self.out.write('    <header>\n')
        self.out.write('        <h1>MotoGP Desktop</h1>\n')
        self.out.write('    </header>\n')
        self.out.write('    <main>\n')

    def epilogo(self):
        self.out.write('    </main>\n')
        self.out.write('</body>\n')
        self.out.write('</html>\n')

    def escribir_circuito(self, root, ns):
        # Atributos del elemento raíz (se obtienen directamente con get())
        nombre = root.get("nombre")
        pais = root.get("pais")
        localidad = root.get("localidad_proxima")
        vueltas = root.get("vueltas")

        # Elementos (se obtienen con XPath mediante find() o findall())
        longitud = root.find(".//ns:longitud_circuito", namespaces=ns)
        anchura = root.find(".//ns:anchura_media", namespaces=ns)
        fecha = root.find(".//ns:fecha_2025", namespaces=ns)
        hora = root.find(".//ns:hora_carrera_españa", namespaces=ns)
        patrocinador = root.find(".//ns:patrocinador", namespaces=ns)
        ganador = root.find(".//ns:vencedor/ns:piloto", namespaces=ns)
        tiempo = root.find(".//ns:vencedor/ns:tiempo", namespaces=ns)

        referencias = root.findall(".//ns:referencias/ns:referencia", namespaces=ns)
        fotos = root.findall(".//ns:fotos/ns:foto", namespaces=ns)
        videos = root.findall(".//ns:videos/ns:video", namespaces=ns)
        clasificacion = root.findall(".//ns:clasificacion_mundial/ns:piloto", namespaces=ns)

        # Escritura del contenido
        self.out.write(f'        <h2>Circuito: {nombre}</h2>\n')
        self.out.write(f'        <p>Localidad próxima: {localidad} ({pais})</p>\n')
        self.out.write(f'        <p>Vueltas: {vueltas}</p>\n')
        self.out.write(f'        <p>Longitud del circuito: {longitud.text} {longitud.get("unidad")}</p>\n')
        self.out.write(f'        <p>Anchura media: {anchura.text} {anchura.get("unidad")}</p>\n')
        self.out.write(f'        <p>Fecha de la carrera: {fecha.text}</p>\n')
        self.out.write(f'        <p>Hora en España: {hora.text}</p>\n')
        self.out.write(f'        <p>Patrocinador: {patrocinador.text}</p>\n')
        self.out.write(f'        <p>Vencedor: {ganador.text} ({tiempo.text})</p>\n')

        # Clasificación
        self.out.write('        <h3>Clasificación Mundial</h3>\n')
        self.out.write('        <ol>\n')
        for piloto in clasificacion:
            self.out.write(f'            <li>{piloto.text}</li>\n')
        self.out.write('        </ol>\n')

        # Referencias
        self.out.write('        <h3>Referencias</h3>\n')
        self.out.write('        <ul>\n')
        for ref in referencias:
            url = ref.text.strip()
            self.out.write(f'            <li><a href="{url}">{url}</a></li>\n')
        self.out.write('        </ul>\n')
        # Galería de imágenes y videos
        self.out.write('        <h3>Galería</h3>\n')
        for foto in fotos:
            self.out.write(f'            <figure><img src="{foto.text}" alt="Imagen del circuito"></figure>\n')
        for video in videos:
            self.out.write(f'            <video controls><source src="{video.text}" type="video/mp4"></video>\n')

def main():
    try:
        tree = ET.parse("circuitoEsquema.xml")
    except Exception as e:
        print("No se puede abrir 'circuitoEsquema.xml':", e)
        return

    ns = {'ns': 'http://www.uniovi.es'}
    root = tree.getroot()

    html_filename = "../InfoCircuito.html"

    with open(html_filename, "w", encoding="utf-8") as outFile:
        html = Html(outFile)
        html.prologo()
        html.escribir_circuito(root, ns)
        html.epilogo()

    print(f"HTML generado correctamente: {html_filename}")


if __name__ == "__main__":
    main()
