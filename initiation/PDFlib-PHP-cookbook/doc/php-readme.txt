----------------------------
PDFlib Java Cookbook Package
----------------------------

The PDFlib Cookbook is a collection of PDFlib coding fragments for
solving specific problems. It is maintained as a growing list of sample
programs. The Cookbook topics are written in the Java language, but can easily
be ported to other programming languages since the PDFlib API is almost
identical for all supported language bindings. A PHP version of the Cookbook
is available as well. Each Cookbook topic denotes the PDFlib product required
for its execution.


Prerequisites
=============

- The PDFlib binary library for Java must have been installed.

- The Cookbook topics can conveniently be compiled and run with the Java
  build tool Apache Ant (see ant.apache.org).
  Alternatively, you can manually compile and run the samples.
  
- The PDFlib Cookbook package contains an Eclipse project file that allows
  the direct import of the Cookbook into an Eclipse workspace
  (see "Importing the PDFlib Cookbook into Eclipse" below).


Installing the Cookbook
=======================

- Download the PDFlib-cookbook archive from www.pdflib.com and unpack it.
  This will create the following PDFlib Cookbook structure:
  
  PDFlib-cookbook
      / doc
      / extra_input
      / input
      / java
          / build
          / src / com / pdflib / cookbook / pdflib / ... / *.java
          build.xml
      .classpath
      .project    
	    
- Copy "pdflib.jar" and the platform-dependent PDFlib library ("pdflib_java.dll"
  for Windows, "libpdflib_java.so" for Linux etc.) from the "bind/java"
  directory of the PDFlib distribution to the "PDFlib-cookbook/java"
  subdirectory.

- Some samples need additional resources that are not delivered
  with the Cookbook because of license restrictions or file size issues. If
  you want to run these sample programs, please put the required resources
  into the "extra_input" directory:
  
  com.pdflib.cookbook.pdflib.fonts.opentype_features_for_cjk
        Meiryo font from Windows Vista/7: 
                "meiryo.ttc"
        
  com.pdflib.cookbook.pdflib.complex_scripts.gaiji_eudc
        MS Mincho font, available at
        http://www.pdflib.com/download/resources/japanese-resource-kit/:
                "MS Mincho.ttf"
                
  com.pdflib.cookbook.pdflib.complex_scripts.arabic_formatting
  com.pdflib.cookbook.pdflib.fonts.glyph_availability
  com.pdflib.cookbook.pdflib.fonts.opentype_feature_tester
        "Arial Unicode MS" font, available commercially:
                "arialuni.ttf"

  com.pdflib.cookbook.pdflib.complex_scripts.arabic_formatting
        "Tahoma" font from Windows:
                "tahoma.ttf"


Compiling and Executing Cookbook topics with Ant
================================================

- In the java directory of the Cookbook execute the following commands:
  
  ant               to compile and execute all topics. 
                    The class files are stored in the build subdirectories.
                    Any PDF output documents will be created in the current
                    directory.

  ant <topic name>  to compile and execute a particular topic.

  ant build         to compile all topics.
  
  ant clean         to delete all class and PDF files.
  
  
Manually compiling and executing Cookbook topics
================================================

As an alternative to using ant you can compile and run the topics manually
as follows:

- cd to PDFlib-cookbook/java

- Compile a class file as follows: 
  
  On Windows, Linux or Mac (enter command in one line): 
  
  javac -d build -classpath pdflib.jar -sourcepath src
        src/com/pdflib/cookbook/pdflib/general/starter_basic.java

- Execute a class file as follows: 
  
  On Linux or Mac (enter command in one line): 
  
  java -Djava.library.path=. -classpath build:pdflib.jar
        com.pdflib.cookbook.pdflib.general.starter_basic
  
  On Windows (enter command in one line):
  
  java -Djava.library.path=. -classpath build;pdflib.jar
        com.pdflib.cookbook.pdflib.general.starter_basic
  
 
Importing the PDFlib Cookbook project into Eclipse
==================================================

- Choose "File, Import, General, Existing Projects into Workspace" and
  press "Next".

- Click on "Select archive file:" and browse to the PDFlib Cookbook archive file
  ("PDFlib-cookbook.tar.gz" or "PDFlib-cookbook.zip").

  The project "PDFlib-cookbook" will be displayed in the "Projects:" section.

- Click "Finish" to import the project into your workspace.
  
- Copy "pdflib.jar" and the platform-dependent PDFlib library ("pdflib_java.dll"
  for Windows, "libpdflib_java.so" for Linux etc.) from the "bind/java"
  directory of the PDFlib distribution to the "java" subdirectory of the
  "PDFlib-cookbook" project.
  
- Refresh "PDFlib-cookbook"
  
- To use the included build.xml Ant build file:

  + Open the "Ant" view in the "Java" perspective.
  
  + Right-click in the "Ant" view and select "Add Buildfiles...".
  
  + Browse to the "build.xml" file in the "java" subdirectory of the
    "pdflib-cookbook" project. 
    
  + Run individual or all topics by clicking on the corresponding entry in the 
    "Ant" view.


Enabling the PDF/VT-specific examples
=====================================

The PDFlib Cookbook contains source code in the Java package
"com.pdflib.cookbook.pdflib.pdfvt" that only can be compiled and executed
with PDFlib 8 VT Edition. This package is disabled from compilation by
default. To enable compilation and execution please do the following:

- For Ant, add the property definition "-Dpdflib-vt=true" to the command line,
  e.g: 
        
        ant -Dpdflib-vt=true

- In the Eclipse project, remove the exclusion of the package
  "com.pdflib.cookbook.pdflib.pdfvt" from the project's properties:
  
  + Right-click on the "PDFlib-cookbook" Java project, select "Properties".
  
  + In the project's properties, click on "Java Build Bath".
  
  + Select the source tab and expand the source folder
    "PDFlib-cookbook/java/src".
  
  + Double-click the "Excluded" item and in the dialog that opens remove
    "com/pdflib/cookbook/pdflib/pdfvt/" from the "Exclusion patterns" list.
    
For more information on PDF/VT and PDFlib 8 VT Edition please visit:

        http://www.pdflib.com/knowledge-base/pdfvt/
