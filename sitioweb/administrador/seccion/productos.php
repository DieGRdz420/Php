<?php include("../template/cabecera.php"); ?>
<?php

    $txtID=(isset($_POST['txtID']))?$_POST['txtID']:"";
    $txtNombre=(isset($_POST['txtNombre']))?$_POST['txtNombre']:"";
    $txtImagen=(isset($_FILES['txtImagen']['name']))?$_FILES['txtImagen']['name']:"";
    $accion=(isset($_POST['accion']))?$_POST['accion']:"";

    include("../config/db.php");

    switch ($accion) {
        case 'Agregar':
            $sentenciaSQL = $conexion -> prepare("INSERT INTO productos (nombre, imagen) VALUES (:nombre, :imagen);");
            $sentenciaSQL -> bindParam(':nombre', $txtNombre);

            $fecha = new DateTime();
            $nombreArchivo = ($txtImagen!="")?$fecha->getTimestamp()."_".$_FILES["txtImagen"]["name"]:"imagen.jpg";

            $tmpImagen = $_FILES["txtImagen"]["tmp_name"];
            if ($tmpImagen!="") {
                move_uploaded_file($tmpImagen,"../../img/".$nombreArchivo);
            };

            $sentenciaSQL -> bindParam(':imagen', $nombreArchivo);
            $sentenciaSQL -> execute();
            header("Location: productos.php");
            break;

        case 'Modificar':
            $sentenciaSQL=$conexion -> prepare("UPDATE productos SET nombre=:nombre WHERE id=:id");
            $sentenciaSQL -> bindParam(':nombre', $txtNombre);
            $sentenciaSQL -> bindParam(':id', $txtID);
            $sentenciaSQL -> execute();

            if($txtImagen != "") {
                $fecha = new DateTime();
                $nombreArchivo = ($txtImagen!="")?$fecha->getTimestamp()."_".$_FILES["txtImagen"]["name"]:"imagen.jpg";
                $tmpImagen = $_FILES["txtImagen"]["tmp_name"];

                move_uploaded_file($tmpImagen,"../../img/".$nombreArchivo);

                $sentenciaSQL=$conexion -> prepare("SELECT imagen FROM productos WHERE id=:id");
                $sentenciaSQL -> bindParam(':id', $txtID);
                $sentenciaSQL -> execute();
                $product=$sentenciaSQL->fetch(PDO::FETCH_LAZY);

                if ( isset($product["imagen"]) &&($product["imagen"] != "imagen.jpg") ) {
                    if(file_exists("../../img/".$product["imagen"])) {
                        unlink("../../img/" . $product["imagen"]);
                    };
                };

                $sentenciaSQL=$conexion -> prepare("UPDATE productos SET imagen=:imagen WHERE id=:id");
                $sentenciaSQL -> bindParam(':imagen', $nombreArchivo);
                $sentenciaSQL -> bindParam(':id', $txtID);
                $sentenciaSQL -> execute();
            };
            header("Location: productos.php");
            break;

        case 'Cancelar':
            header("Location: productos.php");
            break;

        case 'Seleccionar':
            $sentenciaSQL=$conexion -> prepare("SELECT * FROM productos WHERE id=:id");
            $sentenciaSQL -> bindParam(':id', $txtID);
            $sentenciaSQL -> execute();
            $product=$sentenciaSQL->fetch(PDO::FETCH_LAZY);

            $txtNombre=$product['nombre'];
            $txtImagen=$product['imagen'];
            //echo "presiono Cancelar";
            break;

        case 'Borrar':
            $sentenciaSQL=$conexion -> prepare("SELECT imagen FROM productos WHERE id=:id");
            $sentenciaSQL -> bindParam(':id', $txtID);
            $sentenciaSQL -> execute();
            $product=$sentenciaSQL->fetch(PDO::FETCH_LAZY);

            if ( isset($product["imagen"]) &&($product["imagen"] != "imagen.jpg") ) {
                if(file_exists("../../img/".$product["imagen"])) {
                    unlink("../../img/" . $product["imagen"]);
                };
            };

            $sentenciaSQL = $conexion -> prepare("DELETE FROM productos WHERE id=:id");
            $sentenciaSQL -> bindParam(':id', $txtID);
            $sentenciaSQL -> execute();
            header("Location: productos.php");
            break;
    }

    $sentenciaSQL=$conexion -> prepare("SELECT * FROM productos");
    $sentenciaSQL -> execute();
    $listaproductos=$sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="col-md-5">

    <div class="card">
        <div class="card-header">
            Datos de Libros
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="txtID">ID:</label>
                    <input required readonly type="text" name="txtID" id="txtID" class="form-control" placeholder="ID" value="<?php echo $txtID; ?>" >
                </div>

                <div class="form-group">
                    <label for="txtNombre">Nombre:</label>
                    <input required type="text" name="txtNombre" id="txtNombre" class="form-control" placeholder="Nombre" value="<?php echo $txtNombre; ?>" >
                </div>

                <div class="form-group">
                    <label for="txtImagen">Imagen:</label>
                    
                    <br />

                    <?php if($txtImagen != "") { ?>
                        <img class="img-thumbnail rounded" src="../../img/<?php echo $txtImagen; ?>" alt="" width="50" />
                    <?php } ?>
                    

                    <input type="file" name="txtImagen" id="txtImagen" class="form-control" placeholder="Imagen" >
                </div>

                <div class="btn-group" role="group" aria-label="">
                    <button type="submit" class="btn btn-success" name="accion" <?php echo ($accion == "Seleccionar")?"disabled":""; ?>  value="Agregar" >Agregar</button>
                    <button type="submit" class="btn btn-warning" name="accion" <?php echo ($accion != "Seleccionar")?"disabled":""; ?> value="Modificar" >Modificar</button>
                    <button type="submit" class="btn btn-info" name="accion" <?php echo ($accion != "Seleccionar")?"disabled":""; ?> value="Cancelar" >Cancelar</button>
                </div>
            </form>
        </div>
    </div>

</div>
<div class="col-md-7">
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Imagen</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($listaproductos as $producto) { ?>
            <tr>
                <td><?php echo $producto['id']; ?></td>
                <td><?php echo $producto['nombre']; ?></td>
                <td>
                    <img class="img-thumbnail rounded" src="../../img/<?php echo $producto['imagen']; ?>" alt="../../img/<?php echo $producto['imagen']; ?>" width="50" >
                </td>
                <td>
                    
                    <form method="POST" >
                        <input type="hidden" name="txtID" id="txtID"  value="<?php echo $producto['id']; ?>" />
                        <input type="submit" name="accion" value="Seleccionar" class="btn btn-primary"   />
                        <input type="submit" name="accion" value="Borrar" class="btn btn-danger"   />
                    </form>

                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

</div>


<?php include("../template/pie.php"); ?>