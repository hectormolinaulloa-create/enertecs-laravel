# Diseño: Página de Resultados Calculadora Solar On-Grid

## Contexto

La calculadora solar ya funciona end-to-end (subida de boleta → extracción con IA → cálculo → step 4). El step 4 actual muestra solo 4 métricas básicas. Este rediseño transforma ese paso en una página de conversión profesional que entrega valor real al cliente y lo motiva a contactar a Enertecs.

**Audiencia:** Propietario residencial chileno que subió su boleta eléctrica.  
**Objetivo de conversión:** Que el cliente contacte a Felipe Araya (Ingeniero de Desarrollo de Negocio) por WhatsApp para avanzar con la cotización.

---

## 1. Página de Resultados (Step 4)

### Estructura visual (de arriba hacia abajo)

**Encabezado azul (`#0067FF`)**
- Línea pequeña: "Enertecs SpA · Análisis solar con IA"
- Título: "Buenas noticias, {nombre_cliente}."
- Subtítulo: "Preparamos su estimación solar en base a su boleta de {distribuidora}."

**Tarjeta flotante de ahorro (hero KPI)**
- Fondo blanco, sombra, se superpone 12px sobre el encabezado
- Texto: "Si instala un sistema solar hoy, usted podría ahorrar"
- Cifra grande (36px, negrita): `${ahorro_mensual_clp}` formateado en pesos CLP
- Subtexto: "al mes — **desde el primer día de operación**"

**3 KPIs iguales** (grid 1fr 1fr 1fr, tarjetas blancas)
- **Ahorro mensual** (azul): `${ahorro_mensual_clp}` + "Lo que deja de pagarle a la distribuidora cada mes"
- **Recupera inversión** (verde): `{payback_anios} años` + "Después de ese plazo, la energía solar es sin costo"
- **Baja su boleta** (morado): `−{porcentaje_reduccion}%` + "De ${costo_sin_solar} a aproximadamente ${costo_con_solar} al mes"

**Simulación de boleta**
- Título: "Así se vería su boleta"
- Barra roja alta (boleta actual) vs. barra verde pequeña (con solar), con valores en CLP
- Nota al pie: "Estimación basada en su consumo histórico y el precio real de su tarifa"

**Su sistema solar** (tarjeta blanca, grid 2 columnas)
- Paneles: `{n_paneles} × {modelo_panel}`
- Inversor: `{modelo_inversor}` (si `n_inversores > 1`: `{n_inversores} × {modelo_inversor} en paralelo`)
- Potencia total: `{potencia_kwp} kWp`
- Área de techo: `~{area_m2} m²`
- CO₂ evitado: `{co2_kg} kg al año`

**Teaser del PDF** (tarjeta con borde izquierdo azul)
- Título: "Su informe completo incluye además"
- Subtítulo: "Descargue el PDF para ver el detalle técnico y constructivo de su proyecto."
- Lista de 6 ítems:
  1. Dimensiones físicas del inversor (para la instalación)
  2. Metros cuadrados exactos que necesita en su techo
  3. Configuración eléctrica: strings y cableado
  4. Proyección de ahorro a 25 años
  5. Ficha técnica completa del panel solar
  6. Datos de contacto de Enertecs SpA
- Botón azul: "Descargar informe PDF"

**CTA Felipe Araya**
- Fondo azul claro (`#eff6ff`), borde `#bfdbfe`
- Título: "¿Le interesa avanzar?"
- Subtítulo: "Nuestro ingeniero lo asesora sin compromiso."
- Avatar circular "FA" + nombre "Felipe Araya" + cargo "Ingeniero de Desarrollo de Negocio"
- Botón verde WhatsApp: "Escribirle por WhatsApp" → `https://wa.me/56935165830?text=...` (mensaje pre-llenado con nombre del cliente y ahorro estimado)
- Teléfono: +56 9 3516 5830

**Pie de página**
- Botón secundario: "Nueva consulta" (borde gris, reinicia wizard)

### Datos que necesita el componente Livewire

Todos provienen de `$resultados` (ya calculado por `OngridCalculator`):
- `ahorro_mensual_clp` = `costo_sin_solar_clp - costo_con_solar_clp`
- `porcentaje_reduccion` = `round((ahorro / costo_sin_solar) * 100)`
- `costo_sin_solar_clp` = `consumo_efectivo * precio_kwh_clp`
- `costo_con_solar_clp` = costo restante tras cobertura solar
- `payback_anios` ya existe en `$resultados`
- `n_inversores` ya existe en `$resultados`
- Todos los demás campos ya existen

---

## 2. Correo automático a Felipe Araya

**Cuándo:** Al completar el cálculo (cuando se llega a step 4).  
**Destino:** `felipe.araya@enertecs.cl`  
**Asunto:** `Nuevo lead solar — {nombre_cliente} ({ahorro_mensual_clp}/mes)`  
**Formato:** HTML simple (no depende de diseño complejo).

### Contenido del correo HTML

- Nombre del cliente, teléfono, email
- Ahorro mensual estimado (CLP)
- Sistema dimensionado (paneles, inversor, kWp)
- Distribuidora y región
- Nota: el cliente dio su consentimiento para que Enertecs use sus datos de contacto

**Implementación:** Mailable `NuevoLeadSolarMail` despachado como `Mail::to(...)->queue(...)` dentro de `CalculadoraWizard::calcular()` al llegar a step 4.

---

## 3. Checkbox de consentimiento (Step 3)

En el formulario de confirmación de datos (step 3), añadir al final:

> ☐ Autorizo a Enertecs SpA a utilizar mis datos de contacto para hacerme llegar información sobre mi proyecto solar.

- Campo Livewire: `$consentimiento` (boolean, requerido para avanzar)
- Si no está marcado, el botón "Continuar" muestra error de validación
- El valor se guarda en `calculadora_solicitudes.consentimiento` (columna booleana nueva)

---

## 4. Mejoras al PDF (`informe-ongrid.blade.php`)

El PDF ya existe. Se agregan las siguientes secciones:

**Datos constructivos del inversor**
- Modelo, potencia, dimensiones físicas (Alto × Ancho × Fondo en mm), peso
- Fuente: `GoodweCatalog::inversores()` — agregar campo `dimensiones` a cada modelo

**Datos constructivos del panel**
- Modelo, potencia Wp, dimensiones (mm), peso
- Fuente: catálogo de paneles en `OngridCalculator` — ya existe, agregar campo `dimensiones`

**Configuración eléctrica**
- Strings, paneles por string, cableado recomendado
- Ya calculado por `OngridCalculator`

**Proyección de ahorro a 25 años**
- Tabla simple: año 1, 5, 10, 15, 20, 25 — ahorro acumulado en CLP
- Asume 0.5% degradación anual del panel, 3% alza anual del precio eléctrico

**Ficha técnica del panel**
- Datos del catálogo: Voc, Isc, Vmp, Imp, eficiencia, garantía

**Datos de contacto Enertecs**
- Felipe Araya, +56 9 3516 5830, felipe.araya@enertecs.cl, enertecs.cl

---

## 5. Campo `porcentaje_reduccion` en `OngridCalculator`

Agregar al array `$resultados` devuelto:
```php
'porcentaje_reduccion' => round(($ahorroMensual / $costoSinSolar) * 100),
'costo_sin_solar_clp'  => round($costoSinSolar),
'costo_con_solar_clp'  => round($costoSinSolar - $ahorroMensual),
```

---

## Alcance excluido

- No se implementa animación de "reveal" ni confeti (el diseño celebratorio se logra con la tipografía y los colores).
- No se usa charting library externa — la simulación de boleta es CSS puro.
- El correo a Felipe no incluye el PDF adjunto (solo el resumen HTML).
- No se implementa una tabla de ahorro interactiva en pantalla; esa proyección solo va en el PDF.
